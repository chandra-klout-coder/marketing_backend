const { sendResponse, distanceBt2Points } = require("../../utils/utility");
const { FCMMessaging } = require("../../utils/firebase-notification");
const UserModel = require("../../models/user-model");
const DeleteUserModel = require("../../models/deleted-user-model");
const ConnectionRequestsModel = require("../../models/connection-requests");
const ConnectionsModel = require("../../models/connections");
const MarkedUser = require("../../models/marked-users");
const OTPTemp = require("../../models/otp-temp");
const Notification = require("../../models/notifications");
const jwt = require("jsonwebtoken");
const bcrypt = require("bcryptjs");
const moment = require("moment");
const imageBaseUrl = process.env.DOWNLOAD_IMAGE_BASE_URL;

const register = async (req, res) => {

  const { name, email, linkedInId } = req.body;

  // Validate user input
  if (!(email && linkedInId && name)) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "email, name and password required!",
    });
  }

  const oldUser = await UserModel.findOne({
    emailId: email,
    linkedInId: linkedInId,
  });

  if (oldUser) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "User Already Exist. Please Login",
    });
  }

  const encryptedPassword = await bcrypt.hash(linkedInId, 10);

  const data = new UserModel({
    name: req.body.name,
    emailId: req.body.email,
    mobileNumber: req.body.mobileNumber,
    profileImage: req.body.profileImage,
    company: req.body.company,
    designation: req.body.designation,
    industryName: req.body.industryName,
    industryId: req.body.industryId,
    location: req.body.location,
    linkedInId: req.body.linkedInId,
    linkedInAccessToken: req.body.linkedInAccessToken,
    deviceToken: req.body.deviceToken,
    deviceVersion: req.body.deviceVersion,
    deviceType: req.body.deviceType,
    deviceName: req.body.deviceName,
    appVersion: req.body.appVersion,
    latitude: "",
    preferred_skills: "",
    longitude: "",
    city: "",
    cityId: null,
    searchDistanceinKm: 2,
    shareLastSeen: 1,
    whatsAppNotifications: 1,
    status: 1,
    isDeactivate: 0,
    password: encryptedPassword,
  });

  try {
    const user = await data.save();
    // Create token
    const token = jwt.sign(
      { user_id: user._id, email },
      process.env.TOKEN_KEY
      // {
      //     expiresIn: "1h",
      // }
    );
    // save user token
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "User registered successfully",
      data: { user, token, imageBaseUrl },
    });
  } catch (error) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 400,
      msg: error.message,
      data: error,
    });
  }
};
const getAllUsers = async (req, res) => {
  try {
    const data = await UserModel.find({}, { password: false, __v: false });
   
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "User list retrieved successfully",
      data: { data, imageBaseUrl },
    });

  } catch (error) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: error.message,
    });
  }
};
const login = async (req, res) => {
  try {
    // Get user input
    const { email, linkedInId } = req.body;

    // Validate user input
    if (!(email && linkedInId)) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 200,
        msg: "email and password is required",
      });
    }

    // Validate if user exist in our database
    const user = await UserModel.findOne(
      { emailId: email, linkedInId: linkedInId },
      { __v: false }
    );

    if (user) {
      // Create token
      const token = jwt.sign(
        { user_id: user._id, email },
        process.env.TOKEN_KEY
        // {
        //     expiresIn: "1000h",
        // }
      );
      const result = await UserModel.updateOne(
        { emailId: email, linkedInId: linkedInId },
        {
          $set: { isDeactivate: 0 },
        }
      );

      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "logged in successfully!",
        data: { user, token, imageBaseUrl },
      });
    }
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Account does not exist.",
    });

  } catch (err) {
    console.log(err);

    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const userUpdate = async (req, res) => {
  try {
    // Get user input
    const request = req.body;
    request.updatedAt = moment().format("YYYY-MM-DD HH:mm:ss");
    const id = req.headers["UserId"] || req.headers["userid"];
    // Validate user input
    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 200,
        msg: "User Id is missing.",
      });
    }
    const result = await UserModel.updateOne(
      { _id: id },
      {
        $set: request,
      }
    );
    if (request.awards) {
      var awards = await UserModel.findOne({ _id: id }, { awards: true });
      awards = awards.awards;
      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "Data updated successfully!",
        data: { awards },
      });
    }
    if (request.skills) {
      var skills = await UserModel.findOne({ _id: id }, { skills: true });
      skills = skills.skills;
      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "Data updated successfully!",
        data: { skills },
      });
    }
    if (request.featured) {
      var featured = await UserModel.findOne({ _id: id }, { featured: true });
      featured = featured.featured;
      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "Data updated successfully!",
        data: { featured },
      });
    }
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Data updated successfully!",
      data: {},
    });
  } catch (err) {
    console.log(err);
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const getFullProfileDetails = async (req, res) => {
  try {
    const id = req.headers["UserId"] || req.headers["userid"];
    // Validate user input
    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "User Id is missing.",
      });
    }

    var details = await UserModel.findOne(
      { _id: id },
      { password: false, __v: false }
    );
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Data updated successfully!",
      data: { details, imageBaseUrl },
    });
  } catch (err) {
    console.log(err);
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const nearbyProfiles = async (req, res) => {

  try {
    
    const id = req.headers["UserId"] || req.headers["userid"];

    const { latitude, longitude, distance } = req.body;

    // Validate user input
    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "User Id is missing.",
      });
    }

    var userData = await UserModel.find(
      { status: 1, _id: id },
      { password: false, __v: false }
    );

    // console.log(userData);
    //{skills : {$regex:"Multilingual abilities"}, skills : {$regex:"Teamwork and collaboration"}}

    var skillsName = [];
    var industriesName = [];

    if (userData.length) {
      var userSkills = userData[0].skills;

      if (userSkills) {
        userSkills = JSON.parse(userSkills);
        if (userSkills.length) {
          userSkills.map(function (val, key) {
            if (val.SkillName) {
              skillsName.push({ skills: { $regex: val.SkillName } });
            }
          });
        }
      }

      var userIndustries = userData[0].industry;

      if (userIndustries) {
        userIndustries = JSON.parse(userIndustries);
        if (userIndustries.length) {
          userIndustries.map(function (val, key) {
            if (val.IndustryId) {
              skillsName.push({ industryId: val.IndustryId });
            }
          });
        }
      }
    }

    console.log("skillsName", skillsName);
    // var skills = { $or: [{ skills: { $regex: "Multilingual abilities" } }, { skills: { $regex: "Teamwork and collaboration" } }] }

    if (skillsName.length) {
      var list = await UserModel.find(
        {
          status: 1,
          _id: { $ne: id },
          latitude: { $ne: "" },
          longitude: { $ne: "" },
          isDeactivate: 0,
          $or: skillsName,
        },
        { password: false, __v: false }
      );
    } else {
      var list = await UserModel.find(
        {
          status: 1,
          _id: { $ne: id },
          latitude: { $ne: "" },
          longitude: { $ne: "" },
          isDeactivate: 0,
        },
        { password: false, __v: false }
      );
    }

    var nearBy = [];
    var userIds = [];

    if (list) {
      list.map((value, index) => {
        const nearDistance = distanceBt2Points(
          latitude,
          longitude,
          value.latitude,
          value.longitude
        );
        var userData = {
          cityId: value.cityId,
          _id: value._id,
          name: value.name,
          emailId: value.emailId,
          mobileNumber: value.mobileNumber,
          profileImage: value.profileImage,
          company: value.company,
          designation: value.designation,
          industryId: value.industryId,
          location: value.location,
          linkedInId: value.linkedInId,
          linkedInAccessToken: value.linkedInAccessToken,
          deviceToken: value.deviceToken,
          deviceVersion: value.deviceVersion,
          appVersion: value.appVersion,
          deviceType: value.deviceType,
          deviceName: value.deviceName,
          latitude: value.latitude,
          longitude: value.longitude,
          city: value.city,
          status: value.status,
          whatsAppNotifications: value.whatsAppNotifications,
          shareLastSeen: value.shareLastSeen,
          searchDistanceinKm: value.searchDistanceinKm,
          industryName: value.industryName,
          createdAt: value.createdAt,
          updatedAt: value.updatedAt,
          aboutMe: value.aboutMe,
          professionalHighlight: value.professionalHighlight,
          awards: value.awards,
          featured: value.featured,
          skills: value.skills,
          preferred_skills: value.preferred_skills,
        }; //value;
        var inDistance = nearDistance;
        userData.distanceBetween = parseFloat(inDistance).toFixed(2);
        if (nearDistance <= distance) {
          nearBy.push(userData);
          userIds.push(userData._id);
        }
      });
    }

    const filter = { request_user_id: id };

    var connReqList = await ConnectionRequestsModel.find(filter);

    const filter2 = { $or: [{ from_user_id: id }, { to_user_id: id }] };

    var connectionsList = await ConnectionsModel.find(filter2, { __v: false });

    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Data list successfully!",
      data: {
        nearByProfile: nearBy,
        connectionRequests: connReqList,
        connectionsList: connectionsList,
      },
    });
  } catch (err) {
    console.log(err);
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: "Error in api",
      error: err,
    });
  }
};
const myConnectionsRequest = async (req, res) => {
  try {
    const id = req.headers["UserId"] || req.headers["userid"];
    // Validate user input
    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "User Id is missing.",
      });
    }
    var connReqList = await ConnectionRequestsModel.find(
      { status: 1, request_user_id: id },
      { __v: false }
    );
    var connectionsList = await ConnectionsModel.find(
      { $or: [{ from_user_id: id }, { to_user_id: id }] },
      { __v: false }
    );
    var markedUser = await MarkedUser.find(
      { from_user_id: id },
      { __v: false }
    );

    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Data list successfully!",
      data: {
        connectionRequests: connReqList,
        connectionsList: connectionsList,
        markedUser: markedUser,
      },
    });
  } catch (err) {
    console.log(err);
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const getUserProfileDetails = async (req, res) => {
  try {
    const id = req.headers["UserId"] || req.headers["userid"];
    const { userId, latitude, longitude } = req.body;
    // Validate user input
    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "User Id is missing.",
      });
    }
    details = await UserModel.findOne(
      { _id: userId },
      { password: false, __v: false }
    );
    if (!details) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "Data not found.",
      });
    }
    const nearDistance = await distanceBt2Points(
      latitude,
      longitude,
      details.latitude,
      details.longitude
    );
    details = {
      cityId: details.cityId,
      _id: details._id,
      name: details.name,
      emailId: details.emailId,
      mobileNumber: details.mobileNumber,
      profileImage: details.profileImage,
      company: details.company,
      designation: details.designation,
      industryId: details.industryId,
      location: details.location,
      linkedInId: details.linkedInId,
      linkedInAccessToken: details.linkedInAccessToken,
      deviceToken: details.deviceToken,
      deviceVersion: details.deviceVersion,
      appVersion: details.appVersion,
      deviceType: details.deviceType,
      deviceName: details.deviceName,
      city: details.city,
      createdAt: details.createdAt,
      updatedAt: details.updatedAt,
      latitude: details.latitude,
      longitude: details.longitude,
      searchDistanceinKm: details.searchDistanceinKm,
      shareLastSeen: details.shareLastSeen,
      whatsAppNotifications: details.whatsAppNotifications,
      industry: details.industry,
      skills: details.skills,
      preferred_skills: details.preferred_skills,
      aboutMe: details.aboutMe,
      industryName: details.industryName,
      professionalHighlight: details.professionalHighlight,
      awards: details.awards,
      featured: details.featured,
      status: details.status,
      images: details.images,
      distanceBetween: parseFloat(nearDistance).toFixed(2),
    };
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Data updated successfully!",
      data: { details, imageBaseUrl },
    });
  } catch (err) {
    console.log(err);
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const removeMyConnecton = async (req, res) => {
  try {
    const id = req.headers["UserId"] || req.headers["userid"];
    const { userId, connectionId } = req.body;
    // Validate user input
    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "User Id is missing.",
      });
    }
    if (!connectionId) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "Request is missing.",
      });
    }
    // $or: [{ from_user_id: id }, { to_user_id: userId }]
    var details = await ConnectionsModel.deleteOne({ _id: connectionId });
    // var details = await ConnectionsModel.deleteOne({ $or: [{ from_user_id: id }, { to_user_id: userId }], $or: [{ from_user_id: userId }, { to_user_id: id }] });
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Connection removed successfully.",
      data: { details },
    });
  } catch (err) {
    console.log(err);
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const markedUser = async (req, res) => {
  try {
    const id = req.headers["UserId"] || req.headers["userid"];
    const { userId, status } = req.body;
    // Validate user input
    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "User Id is missing.",
      });
    }
    if (!userId) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "Request is missing.",
      });
    }
    if (status == 1) {
      var details = await MarkedUser.findOne(
        { from_user_id: id, to_user_id: userId },
        { __v: false }
      );
      if (details) {
        return await sendResponse(req, res, {
          successStatus: true,
          statusCode: 200,
          msg: "Connection marked successfully.",
          data: { details },
        });
      }
      try {
        const insertObj = {
          from_user_id: id,
          to_user_id: userId,
          status: 1,
          createdAt: moment().format("YYYY-MM-DD HH:mm:ss"),
          updatedAt: moment().format("YYYY-MM-DD HH:mm:ss"),
        };
        const insertData = new MarkedUser(insertObj);
        const details = await insertData.save();

        return await sendResponse(req, res, {
          successStatus: true,
          statusCode: 200,
          msg: "Connection marked successfully.",
          data: { details },
        });
      } catch (err) {
        console.log(err);
        return await sendResponse(req, res, {
          successStatus: false,
          statusCode: 500,
          msg: err,
        });
      }
    } else {
      var details = await MarkedUser.deleteOne({
        from_user_id: id,
        to_user_id: userId,
      });
      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "Connection unmarked successfully.",
        data: { details },
      });
    }
  } catch (err) {
    console.log(err);
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const bookmarkedList = async (req, res) => {
  try {
    const id = req.headers["UserId"] || req.headers["userid"];
    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: "User Id is missing.",
      });
    }
    var list = await MarkedUser.aggregate([
      {
        $match: { from_user_id: id },
      },
      {
        $lookup: {
          from: "user",
          localField: "to_user_id",
          foreignField: "_id.str",
          as: "userDetails",
        },
      },
    ]);
    var dataList = [];
    var dataList2 = [];
    if (list) {
      for (const key of Object.keys(list)) {
        // console.log(value)
        try {
          // dataList.push(list[key].to_user_id);
          if (list[key].to_user_id == id) {
            dataList.push(list[key].from_user_id);
          } else {
            dataList.push(list[key].to_user_id);
          }
        } catch (error) {
          console.log(error);
        }
      }
      const userProfiles = await UserModel.find(
        { _id: dataList, isDeactivate: 0 },
        { password: false, __v: false }
      );
      var connReqList = await ConnectionRequestsModel.find(
        { status: 1, request_user_id: id },
        { __v: false }
      );
      var connectionsList = await ConnectionsModel.find(
        { $or: [{ from_user_id: id }, { to_user_id: id }] },
        { __v: false }
      );
      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "Bookmarked Connection List.",
        data: {
          markedUsers: list,
          userProfiles,
          connectionRequests: connReqList,
          connectionsList,
        },
      });
    }
  } catch (err) {
    console.log(err);
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const sendOtp = async (req, res) => {
  const { mobileNumber } = req.body;

  // Validate user input
  if (!mobileNumber) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Mobile number required!",
    });
  }

  const oldUser = await UserModel.findOne({ mobileNumber: mobileNumber });

  if (oldUser) {
      return await sendResponse(req, res, { successStatus: false, statusCode: 200, msg: "Mobile number already Exist." })
  }

  const min = 100000;
  const max = 999999;

  const otp = (Math.random() * (max - min) + min).toFixed(0);

  const data = new OTPTemp({
    mobileNumber: req.body.mobileNumber,
    otp: otp,
  });

  try {
    const user = await data.save();

    const accountSid = "AC639a5c40c61975775054f5216fceeb11";
    const authToken = "2a1ed69f8c40e7c1b3ec9ddb31c599db";

    const client = require("twilio")(accountSid, authToken);

    client.messages
      .create({
        body: "Your KLout verification code is:" + otp,
        to: "+91" + req.body.mobileNumber, // Text your number
        from: "+16187452714", // From a valid Twilio number
      })
      .then((message) => console.log(message.sid));
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Otp sent successfully on your mobile number.",
      data: {},
    });
  } catch (error) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 400,
      msg: error.message,
      data: error,
    });
  }
};
const verifyOtp = async (req, res) => {
  const { mobileNumber, OTP } = req.body;
  if (!mobileNumber) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Mobile number required!",
    });
  }
  if (!OTP) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "OTP is required!",
    });
  }
  const verifyOtp = await OTPTemp.findOne({
    mobileNumber: mobileNumber,
    otp: OTP,
  });
  if (!verifyOtp) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "OTP verification failed.",
    });
  } else {
    var details = await OTPTemp.deleteOne({
      mobileNumber: mobileNumber,
      otp: OTP,
    });
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Otp verify successfully.",
      data: {},
    });
  }
};
const connectionRequest = async (req, res) => {
  const { connectionId } = req.body;
  const id = req.headers["UserId"] || req.headers["userid"];

  if (!connectionId) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Connection Id required.",
    });
  }
  const userData = await UserModel.findOne({ _id: id });
  const userConnectionIdData = await UserModel.findOne({ _id: connectionId });
  if (!userData) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "User Id is invalid.",
    });
  }
  const resData = await ConnectionRequestsModel.findOne({
    request_user_id: id,
    receive_user_id: connectionId,
  });
  if (!resData) {
    try {
      var insertData = {
        request_user_id: id,
        receive_user_id: connectionId,
        status: 1,
      };
      const data = new ConnectionRequestsModel(insertData);
      const user = await data.save();
      /** Save Notification    */
      var notificationInsertArr = {
        user_id: connectionId,
        from_user_id: id,
        title: userData.name + ", " + userData.designation,
        body: "Has requested you to connect",
        isRead: 0,
        type: 1,
        data: JSON.stringify({
          Type: "1",
          Title: userData.name + ", " + userData.designation,
          Body: "Has requested you to connect",
          ConnectionId: connectionId,
          RequesterId: id,
          RequestId: user._id,
          Name: userData.name,
          Designation: userData.designation,
          ProfileImage: userData.profileImage,
        }),
      };
      const NotificationData = new Notification(notificationInsertArr);
      const NotificationDataArr = await NotificationData.save();
      // console.log(userConnectionIdData);
      if (userConnectionIdData && userConnectionIdData.deviceToken) {
        console.log(userConnectionIdData.deviceToken);
        console.log(userData.deviceToken);
        /** FCM Notification */
        const FCMMessage = {
          //this may vary according to the message type (single recipient, multicast, topic, et cetera)
          to: userConnectionIdData.deviceToken,
          collapse_key: "",

          notification: {
            title: notificationInsertArr.title,
            body: notificationInsertArr.body,
          },
          data: {
            Type: "1",
            Title: userData.name + ", " + userData.designation,
            Body: "Has requested you to connect",
            ConnectionId: connectionId,
            RequesterId: id,
            RequestId: user._id,
            Name: userData.name,
            Designation: userData.designation,
            ProfileImage: userData.profileImage,
          },
        };
        FCMMessaging(FCMMessage);
        /** FCM Notification */
      }
      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "Request sent.",
        data: {},
      });
    } catch (error) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 400,
        msg: error.message,
        data: error,
      });
    }
  }
  return await sendResponse(req, res, {
    successStatus: false,
    statusCode: 200,
    msg: "Failed to request sent.",
    data: {},
  });
};
const connectionRequestStatusUpdate = async (req, res) => {
  const { connectionReqId, status, notificationId } = req.body;
  const id = req.headers["UserId"] || req.headers["userid"];
  if (!connectionReqId) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Connection Id required!",
    });
  }
  const connectionRequestsData = await ConnectionRequestsModel.findOne({
    _id: connectionReqId,
  });
  if (!connectionRequestsData) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "No connection found.",
    });
  } else {
    /**
     * Insert into connection
     */
    if (status == 1) {
      //Accpet
      const data = ConnectionsModel({
        from_user_id: connectionRequestsData.request_user_id,
        to_user_id: connectionRequestsData.receive_user_id,
        is_deleted: 0,
      });
      const result = await data.save(data);
      const userData = await UserModel.findOne({ _id: id });
      const userToData = await UserModel.findOne({
        _id: connectionRequestsData.request_user_id,
      });
      /** Save Notification    */
      var notificationInsertArr = {
        user_id: connectionRequestsData.request_user_id,
        from_user_id: id,
        title: userData.name + ", " + userData.designation,
        body: "Has accepted your request",
        isRead: 0,
        type: 2,
        data: JSON.stringify({
          Type: "2",
          Title: userData.name + ", " + userData.designation,
          Body: "Has accepted your request",
          ConnectionId: connectionRequestsData.receive_user_id,
          RequesterId: id,
          RequestId: connectionReqId,
          Name: userData.name,
          Designation: userData.designation,
          ProfileImage: userData.profileImage,
        }),
      };
      const NotificationData = new Notification(notificationInsertArr);
      const NotificationDataArr = await NotificationData.save();

      if (userToData && userToData.deviceToken) {
        /** FCM Notification */
        const FCMMessage = {
          //this may vary according to the message type (single recipient, multicast, topic, et cetera)
          to: userToData.deviceToken,
          collapse_key: "",

          notification: {
            title: notificationInsertArr.title,
            body: notificationInsertArr.body,
          },
          data: {
            Type: "2",
            Title: userData.name + ", " + userData.designation,
            Body: "Has requested you to connect",
            ConnectionId: connectionRequestsData.receive_user_id,
            RequesterId: id,
            RequestId: connectionReqId,
            Name: userData.name,
            Designation: userData.designation,
            ProfileImage: userData.profileImage,
          },
        };
        FCMMessaging(FCMMessage);
      }
      /** FCM Notification */
    }
    var details = await ConnectionRequestsModel.deleteOne({
      _id: connectionReqId,
    });
    var details = await Notification.deleteOne({ _id: notificationId });

    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Accepted successfully.",
      data: {},
    });
  }
};
const myConnections = async (req, res) => {
  const id = req.headers["UserId"] || req.headers["userid"];
  if (!id) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Connection Id required!",
    });
  }
  var list = await ConnectionsModel.find(
    { $or: [{ from_user_id: id }, { to_user_id: id }] },
    { __v: false }
  );
  var dataList = [];
  var connectionIdData = [];
  if (list) {
    for (const key of Object.keys(list)) {
      try {
        if (list[key].to_user_id == id) {
          dataList.push(list[key].from_user_id);

          var connectionId = {
            connectionId: list[key]._id,
            userId: list[key].from_user_id,
          };
          connectionIdData.push(connectionId);
        } else {
          dataList.push(list[key].to_user_id);
          var connectionId = {
            connectionId: list[key]._id,
            userId: list[key].to_user_id,
          };
          connectionIdData.push(connectionId);
        }
      } catch (error) {
        console.log(error);
      }
    }
    const userProfiles = await UserModel.find(
      { _id: dataList, isDeactivate: 0 },
      { password: false, __v: false }
    );

    if (!userProfiles) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 200,
        msg: "No connection found.",
      });
    } else {
      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "Connection list",
        data: { connectionIdData, userProfiles },
      });
    }
  }
};
const deactivateAccount = async (req, res) => {
  const id = req.headers["UserId"] || req.headers["userid"];
  const { reason, feedback } = req.body;
  if (!id) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Connection Id required!",
    });
  }
  var list = await ConnectionsModel.find({ _id: id }, { __v: false });

  var request = {
    isDeactivate: 1,
    reason: reason,
    feedback: feedback,
  };
  if (list) {
    const result = await UserModel.updateOne(
      { _id: id },
      {
        $set: request,
      }
    );
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Account deactivated successfully.",
    });
  }
};
const deleteAccount = async (req, res) => {
  try {
    const id = req.headers["UserId"] || req.headers["userid"];
    const { reason, feedback } = req.body;

    if (!id) {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 200,
        msg: "Connection Id required!",
      });
    }
    var details = await UserModel.findOne({ _id: id }, { __v: false });
    if (details) {
      // details.id = details.id;
      var details1 = {
        cityId: details.cityId,
        user_id: details._id,
        name: details.name,
        emailId: details.emailId,
        mobileNumber: details.mobileNumber,
        profileImage: details.profileImage,
        company: details.company,
        designation: details.designation,
        industryId: details.industryId,
        location: details.location,
        linkedInId: details.linkedInId,
        linkedInAccessToken: details.linkedInAccessToken,
        deviceToken: details.deviceToken,
        deviceVersion: details.deviceVersion,
        appVersion: details.appVersion,
        deviceType: details.deviceType,
        deviceName: details.deviceName,
        latitude: details.latitude,
        longitude: details.longitude,
        city: details.city,
        status: details.status,
        password: details.password,
        whatsAppNotifications: details.whatsAppNotifications,
        shareLastSeen: details.shareLastSeen,
        searchDistanceinKm: details.searchDistanceinKm,
        industryName: details.industryName,
        createdAt: details.createdAt,
        updatedAt: details.updatedAt,
        aboutMe: details.aboutMe,
        professionalHighlight: details.professionalHighlight,
        awards: details.awards,
        skills: details.skills,
        featured: details.featured,
        images: details.images,
        industry: details.industry,
        preferred_skills: details.preferred_skills,
        reason: reason,
        feedback: feedback,
      };
      const result = await DeleteUserModel(details1).save();
      var details1 = await UserModel.deleteOne({ _id: id });
      return await sendResponse(req, res, {
        successStatus: true,
        statusCode: 200,
        msg: "Account deleted successfully.",
      });
    } else {
      return await sendResponse(req, res, {
        successStatus: false,
        statusCode: 200,
        msg: "No record found.",
      });
    }
  } catch (err) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 500,
      msg: err,
    });
  }
};
const isActiveAccount = async (req, res) => {
  const id = req.headers["UserId"] || req.headers["userid"];
  const { toUserId } = req.body;
  if (!id) {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Connection Id required!",
    });
  }
  var list = await UserModel.find(
    { _id: toUserId, isDeactivate: 0 },
    { __v: false }
  );
  if (list.length > 0) {
    return await sendResponse(req, res, {
      successStatus: true,
      statusCode: 200,
      msg: "Account is active.",
    });
  } else {
    return await sendResponse(req, res, {
      successStatus: false,
      statusCode: 200,
      msg: "Account is deactivated.",
    });
  }
};

module.exports = {
  isActiveAccount,
  deleteAccount,
  deactivateAccount,
  myConnections,
  connectionRequestStatusUpdate,
  connectionRequest,
  verifyOtp,
  sendOtp,
  bookmarkedList,
  markedUser,
  removeMyConnecton,
  getUserProfileDetails,
  myConnectionsRequest,
  register,
  getAllUsers,
  login,
  getFullProfileDetails,
  nearbyProfiles,
  userUpdate,
};

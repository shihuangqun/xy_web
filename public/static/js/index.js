// (function() {
var validate = common.validate;
var validateMsg = {
  username: '用户名未通过验证',
  phone: '手机号未通过验证',
  cardid: '身份证未通过验证',
};
var userInfo = {
  username: '',
  phone: '',
  cardid: '',
};
var submit_btn = document.querySelector('#submit_btn'),
  info_form = document.querySelector('#info_form'),
  username = document.querySelector('#username'),
  phone = document.querySelector('#phone'),
  cardid = document.querySelector('#cardid'),
  search_history = document.querySelector('#search_history');

// search_history.addEventListener('click', validateFun, { passive: true })
submit_btn.addEventListener('click', validateFun, { passive: true })
username.addEventListener('blur', check, false)
phone.addEventListener('blur', check, false)
cardid.addEventListener('blur', check, false)

// 检测如果有缓存信息,自动填写表单
function setAutoCacheInfo() {
  userInfo = common.storage.getStorage(userInfo, 'wanghei')
  if (userInfo.username || userInfo.phone || userInfo.cardid) {
    username.value = userInfo.username;
    phone.value = userInfo.phone;
    cardid.value = userInfo.cardid;
  }
}


function cacheUserinfo() {
  userInfo = {
    username: username.value,
    phone: phone.value,
    cardid: cardid.value,
  };
  common.storage.setStorage(userInfo, 'wanghei')
}


function check(isCache, dom) {
  layer.closeAll();
  var domId = isCache === 'iscache' ? dom.id : this.id;
  var domVal = isCache === 'iscache' ? dom.value : this.value;

  var msgs = {
    username: {
      funname: 'trueName',
      empty: '用户名不能为空',
      invalid: '请输入正确的2-4位真实姓名'
    },
    phone: {
      funname: 'isPhoneNum',
      empty: '手机号不能为空',
      invalid: '请输入正确的11位手机号码'
    },
    cardid: {
      funname: 'idCard',
      empty: '身份证不能为空',
      invalid: '请输入正确的身份证号'
      // invalid: '请输入正确的身份证号年龄在18-60之间'
    }
  }

  if (!validate.isEmpty(domVal)) {
    validateMsg[domId] = msgs[domId]['empty']
    layer.open({
      content: validateMsg[domId],
      skin: 'msg',
      time: 2
    });
    return false;
  }

  if (!validate[msgs[domId]['funname']](domVal)) {
    validateMsg[domId] = msgs[domId]['invalid']
    layer.open({
      content: validateMsg[domId],
      skin: 'msg',
      time: 2
    });
  } else {
    validateMsg[domId] = true
  }

}

/**
 * 发送请求
 */
function validateFun() {
  var isValid = true;
  errormsg = '';
  // 判断是否勾选用户协议
  if ($("#xieyi-ys .tg-redio").attr("class") == "tg-redio") {
    layer.open({
      content: "请同意报告查询服务协议",
      skin: 'msg',
      time: 2
    });
    return false;
  }
  // 判断是否赠险
  if ($("#xieyi-bx .tg-redio").attr("class") == "tg-redio") {
    $("#upload").val(0);
  }
  // 如果表单从缓存中填写
  checkFromCache()

  _.forIn(validateMsg, function(value, key) {
    if (value !== true) {
      isValid = false
      errormsg = errormsg + ' ' + value
    }
  });

  if (!isValid) {
    layer.open({
      content: errormsg,
      skin: 'msg',
      time: 2
    });
  } else {
    // 缓存用户信息至本地
    cacheUserinfo()
    if (this.id === 'search_history') {
      document.location.href = this.getAttribute('hrefvalue')
    } else {
      // 发送信息查询
      info_form.submit()
    }
  }
}

/**
 * 验证是否为通过缓存自动填写表单
 */
function checkFromCache() {
  check('iscache', username);
  check('iscache', phone);
  check('iscache', cardid);
}






// })()

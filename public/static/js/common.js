var common = (function() {
  var common = {}

  function isEmpty(str) {
    return (str.trim().length > 0) ? true : false;
  }

  function isPhoneNum(str) {
    return /^1[3456789]\d{9}$/.test(str)
  }

  // 真实姓名
  function trueName(str) {
    if(str.length < 2 || str.length > 20){
      return false;
    }
    return /^[\u4E00-\u9FA5A-Za-z.·]+$/.test(str);
    // return /^[\u4e00-\u9fa5]{2,6}$/.test(str)
  }

  //身份证号
  function idCard(code) {
    // var current_year = (new Date).getFullYear();
    // var card_year = code.substr(6,4);
    //
    // var age = current_year-card_year;
    // if(age<18 || age>60){
    //   return false;
    // }

    //身份证号合法性验证 支持15位和18位身份证号 支持地址编码、出生日期、校验位验证
    var city = { 11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江 ", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北 ", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏 ", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外 " };
    var row = true;
    if (!code || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|[xX])$/.test(code)) {
      row = false;
    } else if (!city[code.substr(0, 2)]) {
      row = false;
    } else {
      //18位身份证需要验证最后一位校验位
      if (code.length == 18) {
        code = code.split('');
        //∑(ai×Wi)(mod 11)
        //加权因子
        var factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        //校验位
        var parity = [1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2];
        var sum = 0;
        var ai = 0;
        var wi = 0;
        for (var i = 0; i < 17; i++) {
          ai = code[i];
          wi = factor[i];
          sum += ai * wi;
        }
        if (parity[sum % 11] != code[17].toUpperCase()) {
          row = false;
        }
      }
    }
    if (row) {
      return true;
    } else {
      return false;
    }
  }

  // storage
  function setStorage(infos, tag) {
    for (var key in infos) {
      window.localStorage.setItem(tag + '_' + key, infos[key]);
    }
  }

  function getStorage(infos, tag) {
    for (var key in infos) {
      infos[key] = window.localStorage.getItem(tag + '_' + key);
    }
    return infos;
  }

  // 解析url
  function parseUrl(urlstr) {
    var url = new URL(urlstr);
    var searchParams = new URLSearchParams(url.search);
    var searchObj = {}
    for (var p in searchParams.entries()) {
      searchObj[p[0]] = p[1];
    }
    return {
      searchParams: searchParams,
      searchObj: searchObj
    }
  }

  common = {
    validate: {
      isEmpty: isEmpty,
      isPhoneNum: isPhoneNum,
      trueName: trueName,
      idCard: idCard,
    },
    storage: {
      setStorage: setStorage,
      getStorage: getStorage,
    },
    parseUrl: parseUrl,
  }

  return common
})()

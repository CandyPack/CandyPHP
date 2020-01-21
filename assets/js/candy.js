var _token = '';
var Candy = class Candy {
  test(){
    alert('Hi, World');
  }
  showModal(s){
    $('#' + s).modal('show');
  }
  get(url,callback){
    var data;
    var status;
    $.get(url, function(data, status){
      callback(data,status)
    });
  }
  getToken(){
    $.get(window.location.hostname+'?_candy=token',function(data){
      var result = JSON.parse(JSON.stringify(data));
      _token = result.token;
    });
  }
  token(){
    candy.getToken();
    return _token;
  }
}
var candy = new Candy;
$(function(){
  candy.getToken();
});

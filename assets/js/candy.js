class Candy {
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
      callback(data,status);
  });
}
}
var candy = new Candy;

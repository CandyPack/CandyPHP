var _token = '';
var _page = '';
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
  page(){
    candy.getToken();
    return _page;
  }
  form(id,callback,m){
    $('#'+id).submit(function(e){
      e.preventDefault();
      $('#'+id+' ._candy_form_info').remove();
      $('#'+id+' ._candy').html('');
      var datastring = $("#"+id).serialize()+'&token='+candy.token();
      $.ajax({
        type: $("#"+id).attr('method'),
        url: $("#"+id).attr('action'),
        data: datastring,
        dataType: "json",
        success: function(data) {
          if(data.success){
            if(m===undefined || m){
              if(data.success.result){
                if ($('#'+id+' ._candy_success').length){
                  $('#'+id+' ._candy_success').html(data.success.message);
                }else{
                  $('#'+id).append('<span class="_candy_form_info">'+data.success.message+'</span>');
                }
              }else{
                var errors = data.errors;
                $.each(errors, function(index, value) {
                  if($('#'+id+' ._candy_'+index).length){
                    $('#'+id+' ._candy_'+index).html(value);
                  }else{
                    $('#'+id+' *[name ="'+index+'"]').after('<span class="_candy_form_info" style="color:green">'+value+'</span>');
                  }
                });
              }
            }
            if(callback!==undefined){
              callback(data);
            }
          }
        },
        error: function() {
          alert('Somethings went wrong...');
        }
      });
    });
  }
}
var candy = new Candy;
$(function(){
  candy.getToken();
});


/*
var _history = [];
$(document).on("click","a", function(e) {
  e.preventDefault();
  var url_now = window.location.href;
  var url_go = $(this).attr('href');
  var target = $(this).attr('target');

  if((target==null || target=='_self') && (url_go!='' && url_go.substring(0,11)!='javascript:' && url_go.substring(0,1)!='#') && (!url_go.includes('://') || url_now.split("/")[2]==url_go.split("/")[2])){
    $.ajax({
         url: url_go,
         type: "GET",
         beforeSend: function(xhr){xhr.setRequestHeader('X-CANDY', 'ajaxload');},
         success: function(_data){
             var newDoc = document.open("text/html", "replace");
             newDoc.write(_data);
             newDoc.close();
             _history.push(url_go);
             window.history.pushState(null, document.title, url_go);
             window_location = url_go;
             console.log(_history);
         },
         error : function(){
           $(this).unbind('click');
           e.currentTarget.click();
         }
      });
    }else{
      $(this).unbind('click');
      e.currentTarget.click();
    }
  });*/

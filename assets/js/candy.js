var _candy_token;
var _candy_page;
var _candy_action;
var _candy_forms = [];
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
      callback(data,status);
    });
  }
  getToken(){
    $.get(window.location.hostname+'?_candy=token',function(data){
      var result = JSON.parse(JSON.stringify(data));
      _candy_token = result.token;
    });
  }
  token(){
    if(_candy_token===undefined){
      var req = new XMLHttpRequest();
      req.open('GET', document.location, false);
      req.send(null);
      var headers = req.getAllResponseHeaders().toLowerCase().split("\r\n");
      for (var i = 0, len = headers.length; i < len; i++) {
        var element = headers[i];
        _candy_token = ((element.split(': ')[0])=='x-candy-token') ? element.split(': ')[1] : _candy_token;
      }
    }
    candy.getToken();
    return _candy_token;
  }
  page(){
    if(_candy_page===undefined){
      var req = new XMLHttpRequest();
      req.open('GET', document.location, false);
      req.send(null);
      var headers = req.getAllResponseHeaders().toLowerCase().split("\r\n");
      headers.forEach(element => _candy_page = ((element.split(': ')[0])=='x-candy-page') ? element.split(': ')[1] : _candy_page);
    }
    return _candy_page;
  }
  form(id,callback,m){
    if(_candy_forms.includes(id)){
      return false;
    }else{
      _candy_forms.push(id);
    }
    $(document).on("submit",'#'+id,function(e){
      e.preventDefault();
      $('#'+id+' ._candy_form_info').remove();
      $('#'+id+' ._candy').html('');
      $('#'+id+' ._candy').hide();
      if($('#'+id+' input[type=file]').length > 0){
        var datastring = new FormData();
        $('#'+id+' input').each(function(index){
          if($(this).attr('type')=='file'){
            datastring.append($(this).attr('name'), $(this).prop('files')[0]);
          }else{
            datastring.append($(this).attr('name'), $(this).val());
          }
        });
        datastring.append('token', candy.token());
        var cache = false;
        var contentType = false;
        var processData = false;
      }else{
        var datastring = $("#"+id).serialize()+'&token='+candy.token();
        var cache = true;
        var contentType = "application/x-www-form-urlencoded; charset=UTF-8";
        var processData = true;
      }
      $.ajax({
        type: $("#"+id).attr('method'),
        url: $("#"+id).attr('action'),
        data: datastring,
        dataType: "json",
        contentType: contentType,
        processData: processData,
        cache: cache,
        success: function(data) {
          if(data.success){
            if(m===undefined || m){
              if(data.success.result){
                if ($('#'+id+' ._candy_success').length){
                  $('#'+id+' ._candy_success').show();
                  $('#'+id+' ._candy_success').html(data.success.message);
                }else{
                  $('#'+id).append('<span class="_candy_form_info">'+data.success.message+'</span>');
                }
              }else{
                var errors = data.errors;
                $.each(errors, function(index, value) {
                  if($('#'+id+' ._candy_'+index).length){
                    $('#'+id+' ._candy_'+index).html(value);
                    $('#'+id+' ._candy_'+index).show();
                  }else{
                    $('#'+id+' *[name ="'+index+'"]').after('<span class="_candy_form_info" style="color:'+(data.success.result ? 'green' : 'red')+'">'+value+'</span>');
                  }
                });
              }
            }
            if(callback!==undefined){
              if(typeof callback === "function"){
                callback(data);
              }else if(data.success.result){
                window.location.replace(callback);
              }
            }
          }
        },
        error: function() {
          alert('Somethings went wrong...');
        }
      });
    });
  }
  loader(element,arr,callback){
    $(document).on('click',element,function(e){
      var url_now = window.location.href;
      var url_go = $(this).attr('href');
      var target = $(this).attr('target');
      var page = url_go;
      if((target==null || target=='_self') && (url_go!='' && url_go.substring(0,11)!='javascript:' && url_go.substring(0,1)!='#') && (!url_go.includes('://') || url_now.split("/")[2]==url_go.split("/")[2])){
        e.preventDefault();
        if(_candy_action !== undefined && _candy_action.candy !== undefined && _candy_action.candy.loader.start !== undefined){
          if(_candy_action.candy.loader.start !== undefined && typeof _candy_action.candy.loader.start == 'function'){
            _candy_action.candy.loader.start();
          }
        }
        if(url_go!=url_now){
          window.history.pushState(null, document.title, url_go);
        }
        $.each(arr, function(index, value){
          $.ajax({
            url: url_go,
            type: "GET",
            beforeSend: function(xhr){xhr.setRequestHeader('X-CANDY', 'ajaxload');xhr.setRequestHeader('X-CANDY-LOAD', index);},
            success: function(_data, status, request){
              _candy_page = request.getResponseHeader('x-candy-page');
              $(value).fadeOut(function(){
                $(value).html(_data);
                $(value).fadeIn();
                if(_candy_action !== undefined){
                  if(typeof _candy_action.load == 'function'){
                    _candy_action.load();
                  }
                }
                if(_candy_action.page !== undefined){
                  if(typeof _candy_action.page[_candy_page] == "function"){
                    _candy_action.page[_candy_page]();
                  }
                }
                if(callback!==undefined){
                  callback(candy.page());
                }
              });
            },
            error : function(){
              $(this).unbind('click');
              e.currentTarget.click();
            }
          });
        });
      }
    });
    $(window).on('popstate', function(){
      var url_go = window.location.href;
      if((url_go!='' && url_go.substring(0,11)!='javascript:' && !url_go.includes('#'))){
        $.each(arr, function(index, value){
          $.ajax({
            url: window.location.href,
            type: "GET",
            beforeSend: function(xhr){xhr.setRequestHeader('X-CANDY', 'ajaxload');xhr.setRequestHeader('X-CANDY-LOAD', index);},
            success: function(_data, status, request){
              _candy_page = request.getResponseHeader('x-candy-page');
              $(value).fadeOut(function(){
                $(value).html(_data);
                $(value).fadeIn();
                if(_candy_action !== undefined){
                  if(typeof _candy_action.load == 'function'){
                    _candy_action.load();
                  }
                }
                if(_candy_action.page !== undefined){
                  if(typeof _candy_action.page[_candy_page] == "function"){
                    _candy_action.page[_candy_page]();
                  }
                }
                if(callback!==undefined){
                  callback(candy.page());
                }
              });
            },
            error : function(){
              $(this).unbind('click');
              e.currentTarget.click();
            }
          });
        });
      }
    });
  }
  action(arr){
    if(typeof arr !== 'object'){
      return _candy_action
    }
    _candy_action = arr;
    $.each(arr, function(key, val){
      switch(key){
        case 'load':
          $(function(){ val(); });
          break;
        case 'page':
          $.each(val, function(key2, val2){
            if(key2 == candy.page()){
              $(function(){ val2(); });
            }
          });
          break;
        case 'start':
          $(function(){ val(); });
          break;
        case 'function':
          break;
        default:
          $.each(val, function(key2, val2){
            if((typeof val[key2]) == 'function'){
              $(document).on(key, key2, val[key2]);
            }else{
              var func = '';
              var split = '';
              if(val[key2].includes('.')){
                split = '.';
              }else if(val[key2].includes('#')){
                split = '#';
              }else if(val[key2].includes(' ')){
                split = ' ';
              }
              func = split!='' ? val[key2].split(split) : [val[key2]];
              if(func != ''){
                var getfunc = arr;
                func.forEach(function(item){
                  getfunc = getfunc[item] !== undefined ? getfunc[item] : getfunc[split + item];
                });
                $(document).on(key, key2, getfunc);
              }
            }
          });
      }
    });
  }
}
var candy = new Candy;

var _candy_token;
var _candy_page;
var _candy_action;
var _candy_forms = [];
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
  getToken(){
    if(_candy_token === undefined){
      var req = new XMLHttpRequest();
      req.open('GET', '?_candy=token', false);
      req.setRequestHeader("X_REQUESTED_WITH", "xmlhttprequest");
      req.send(null);
      var req_data = JSON.parse(req.response);
      _candy_page = req_data.page;
      _candy_token = req_data.token;
      return true;
    }
    $.get('?_candy=token',function(data){
      var result = JSON.parse(JSON.stringify(data));
      _candy_token = result.token;
      _candy_page = result.page;
    });
  }
  token(){
    candy.getToken();
    var return_token = _candy_token;
    _candy_token = undefined;
    return return_token;
  }
  page(){
    if(_candy_page===undefined) candy.getToken();
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
      var candy_form = $(this);
      $('#'+id+' ._candy_form_info').remove();
      $('#'+id+' ._candy').html('');
      $('#'+id+' ._candy').hide();
      $('#'+id+' ._candy_error').removeClass('_candy_error');
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
      candy_form.find('button, input[type="button"], input[type="submit"]').prop('disabled',true);
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
                    if(index == '_candy_form'){
                      $('#'+id).append('<span class="_candy_form_info" style="color:red">'+value+'</span>');
                    }else{
                      $('#'+id+' *[name ="'+index+'"]').after('<span class="_candy_form_info" style="color:red">'+value+'</span>');
                    }
                  }
                  $('#'+id+' *[name ="'+index+'"]').addClass('_candy_error');
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
        },
        complete: function() {
          candy_form.find('button, input[type="button"], input[type="submit"]').prop('disabled',false);
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
        $.ajax({
          url: url_go,
          type: "GET",
          beforeSend: function(xhr){xhr.setRequestHeader('X-CANDY', 'ajaxload');xhr.setRequestHeader('X-CANDY-LOAD', Object.keys(arr).join(','))},
          success: function(_data, status, request){
            _candy_page = request.getResponseHeader('x-candy-page');
            $.each(arr, function(index, value){
              $(value).fadeOut(400,function(){
                $(value).html(_data.output[index]);
                $(value).fadeIn();
              });
            });
            setTimeout(function(){
              if(_candy_action !== undefined && typeof _candy_action.load == 'function') _candy_action.load();
              if(_candy_action !== undefined && _candy_action.page !== undefined && typeof _candy_action.page[_candy_page] == "function") _candy_action.page[_candy_page]();
              if(callback!==undefined) callback(candy.page(),_data.variables);
              $("html, body").animate({ scrollTop: 0 });
            },500);
          },
          error : function(){
            window.location.replace(url_go);
          }
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
            beforeSend: function(xhr){xhr.setRequestHeader('X-CANDY', 'ajaxload');xhr.setRequestHeader('X-CANDY-LOAD', Object.keys(arr).join(','));},
            success: function(_data, status, request){
              _candy_page = request.getResponseHeader('x-candy-page');
              $.each(arr, function(index, value){
                $(value).fadeOut(400,function(){
                  $(value).html(_data.output[index]);
                  $(value).fadeIn();
                });
              });
              setTimeout(function(){
                if(_candy_action !== undefined && typeof _candy_action.load == 'function') _candy_action.load();
                if(_candy_action !== undefined && _candy_action.page !== undefined && typeof _candy_action.page[_candy_page] == "function") _candy_action.page[_candy_page]();
                if(callback!==undefined) callback(candy.page(),_data.variables);
              },500);
            },
            error : function(){
              window.location.replace(window.location.href);
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
        case 'interval':
          $.each(val, function(key2, val2){
            $(function(){
              setInterval(function(){
                val2();
              }, key2);
            });
          });
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
candy.getToken();

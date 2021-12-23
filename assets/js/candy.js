var _candy_token,_candy_page,_candy_action,_candy_forms=[],_candy_deprecated=false;class CandyJS{test(){_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1),alert("Hi, World")}showModal(a){_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1),$("#"+a).modal("show")}get(a,n){_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1),$.get(a,function(a,e){n(a,e)})}getToken(a=!1){if(_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1),a){var n=new XMLHttpRequest;n.open("GET","?_candy=token",!1),n.setRequestHeader("X-Requested-With","xmlhttprequest"),n.send(null);var e=JSON.parse(n.response);_candy_page=e.page,_candy_token=e.token}else $.get("?_candy=token",function(a){var n=JSON.parse(JSON.stringify(a));_candy_token=n.token,_candy_page=n.page})}token(){_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1);var a=candy.data();null==_candy_token&&(void 0===_candy_token&&null!==a?(_candy_page=a.candy.page,_candy_token=a.candy.token):candy.getToken(!0));var n=_candy_token;return _candy_token=null,candy.getToken(),n}page(){_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1);var a=candy.data();return null!==a?_candy_page=a.candy.page:candy.getToken(!0),_candy_page}data(){return _candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1),document.cookie.includes("candy=")?JSON.parse(unescape(document.cookie.split("candy=")[1].split(";")[0])):null}form(a,n,e){if(_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1),_candy_forms.includes(a))return!1;_candy_forms.push(a),$(document).on("submit","#"+a,function(c){c.preventDefault();var t=$(this);if($("#"+a+" ._candy_form_info").remove(),$("#"+a+" ._candy").html(""),$("#"+a+" ._candy").hide(),$("#"+a+" ._candy_error").removeClass("_candy_error"),$("#"+a+" input[type=file]").length>0){var o=new FormData;$("#"+a+" input, #"+a+" select, #"+a+" textarea").each(function(a){"file"==$(this).attr("type")?o.append($(this).attr("name"),$(this).prop("files")[0]):o.append($(this).attr("name"),$(this).val())}),o.append("token",Candy.token());var d=!1,s=!1,i=!1}else o=$("#"+a).serialize()+"&token="+Candy.token(),d=!0,s="application/x-www-form-urlencoded; charset=UTF-8",i=!0;t.find('button, input[type="button"], input[type="submit"]').prop("disabled",!0),$.ajax({type:$("#"+a).attr("method"),url:$("#"+a).attr("action"),data:o,dataType:"json",contentType:s,processData:i,cache:d,success:function(c){if(c.success){if(void 0===e||e)if(c.success.result)$("#"+a+" ._candy_success").length?($("#"+a+" ._candy_success").show(),$("#"+a+" ._candy_success").html(c.success.message)):$("#"+a).append('<span class="_candy_form_info">'+c.success.message+"</span>");else{var t=c.errors,o="_candy_error";_candy_action.candy&&_candy_action.candy.form&&_candy_action.candy.form.input&&_candy_action.candy.form.input.class&&_candy_action.candy.form.input.class.invalid&&(o+=" "+_candy_action.candy.form.input.class.invalid);var d="_candy_form_info";_candy_action.candy&&_candy_action.candy.form&&_candy_action.candy.form.span&&_candy_action.candy.form.span.class&&_candy_action.candy.form.span.class.invalid&&(d+=" "+_candy_action.candy.form.span.class.invalid);var s="";_candy_action.candy&&_candy_action.candy.form&&_candy_action.candy.form.span&&_candy_action.candy.form.span.style&&_candy_action.candy.form.span.style.invalid?s+=" "+_candy_action.candy.form.span.style.invalid:_candy_action.candy&&_candy_action.candy.form&&_candy_action.candy.form.span&&_candy_action.candy.form.span.class&&_candy_action.candy.form.span.class.invalid||(s="color:red"),$.each(t,function(n,e){$("#"+a+" ._candy_"+n).length?($("#"+a+" ._candy_"+n).html(e),$("#"+a+" ._candy_"+n).show()):$(`#${a} [candy-form-error="${n}"], [candy-form-error="${n}"][candy-form="${a}"]`).length?($(`#${a} [candy-form-error="${n}"], [candy-form-error="${n}"][candy-form="${a}"]`).html(e),$(`#${a} [candy-form-error="${n}"], [candy-form-error="${n}"][candy-form="${a}"]`).show()):"_candy_form"==n?$("#"+a).append(`<span class="${d}" style="${s}">${e}</span>`):$("#"+a+' *[name ="'+n+'"]').after(`<span class="${d}" style="${s}">${e}</span>`),$("#"+a+' *[name ="'+n+'"]').addClass(o)})}void 0!==n&&("function"==typeof n?n(c):c.success.result&&window.location.replace(n))}},error:function(){console.error("Candy JS:","Somethings went wrong...","\nForm: #"+a+"\nRequest: "+$("#"+a).attr("action"))},complete:function(){t.find('button, input[type="button"], input[type="submit"]').prop("disabled",!1)}})})}loader(a,n,e){_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1),$(document).on("click",a,function(a){var c=window.location.href,t=$(this).attr("href"),o=$(this).attr("target");null!=o&&"_self"!=o||""==t||"javascript:"==t.substring(0,11)||"#"==t.substring(0,1)||t.includes("://")&&c.split("/")[2]!=t.split("/")[2]||(a.preventDefault(),_candy_action.candy&&_candy_action.candy.loader&&_candy_action.candy.loader.start&&_candy_action.candy.loader.start&&"function"==typeof _candy_action.candy.loader.start&&_candy_action.candy.loader.start(),t!=c&&window.history.pushState(null,document.title,t),$.ajax({url:t,type:"GET",beforeSend:function(a){a.setRequestHeader("X-CANDY","ajaxload"),a.setRequestHeader("X-CANDY-LOAD",Object.keys(n).join(","))},success:function(a,c,t){_candy_page=t.getResponseHeader("x-candy-page"),$.each(n,function(n,e){$(e).fadeOut(400,function(){$(e).html(a.output[n]),$(e).fadeIn()})});setTimeout(function(){void 0!==_candy_action&&"function"==typeof _candy_action.load&&_candy_action.load(candy.page(),a.variables),void 0!==_candy_action&&void 0!==_candy_action.page&&"function"==typeof _candy_action.page[_candy_page]&&_candy_action.page[_candy_page](candy.data()),void 0!==e&&e(candy.page(),a.variables),$("html, body").animate({scrollTop:0})},500)},error:function(){window.location.replace(t)}}))}),$(window).on("popstate",function(){var a=window.location.href;""==a||"javascript:"==a.substring(0,11)||a.includes("#")||$.each(n,function(a,c){$.ajax({url:window.location.href,type:"GET",beforeSend:function(a){a.setRequestHeader("X-CANDY","ajaxload"),a.setRequestHeader("X-CANDY-LOAD",Object.keys(n).join(","))},success:function(a,c,t){_candy_page=t.getResponseHeader("x-candy-page"),$.each(n,function(n,e){$(e).fadeOut(400,function(){$(e).html(a.output[n]),$(e).fadeIn()})});setTimeout(function(){void 0!==_candy_action&&"function"==typeof _candy_action.load&&_candy_action.load(),void 0!==_candy_action&&void 0!==_candy_action.page&&"function"==typeof _candy_action.page[_candy_page]&&_candy_action.page[_candy_page](),void 0!==e&&e(Candy.page(),a.variables)},500)},error:function(){window.location.replace(window.location.href)}})})})}action(a){if(_candy_deprecated||(console.error("candy is deprecated please use it as Candy."),_candy_deprecated=1),"object"!=typeof a)return _candy_action;_candy_action=a,$.each(a,function(n,e){switch(n){case"load":$(function(){e()});break;case"page":$.each(e,function(a,n){a==candy.page()&&$(function(){n(candy.data())})});break;case"start":$(function(){e()});break;case"interval":$.each(e,function(a,n){$(function(){setInterval(function(){n()},a)})});break;case"function":case"candy":break;default:$.each(e,function(c,t){if("function"==typeof e[c])$(document).on(n,c,e[c]);else{var o="",d="";if(e[c].includes(".")?d=".":e[c].includes("#")?d="#":e[c].includes(" ")&&(d=" "),""!=(o=""!=d?e[c].split(d):[e[c]])){var s=a;o.forEach(function(a){s=void 0!==s[a]?s[a]:s[d+a]}),$(document).on(n,c,s)}}})}})}}var candy=new CandyJS;
// - DEPRECATED

const Candy = {
  candy: {
    token: {hash:[], data:false},
    page: null,
    actions: {},
    loader: {elements: {}}
  },

  action: function(arr){
    if (typeof arr !== 'object') return this.candy.actions
    this.candy.actions = arr;
    $.each(arr, function(key, val) {
      switch (key) {
        case 'load':
          $(function() { val(); });
          break;
        case 'page':
          $.each(val, function(key2, val2) {
            if (key2 == Candy.page()) $(function() { val2(Candy.data()); });
          });
          break;
        case 'start':
          $(function() { val(); });
          break;
        case 'interval':
          $.each(val, function(key2, val2) {
            $(function() {
              var _i = setInterval(function() {
                val2();
              }, key2);
            });
          });
          break;
        case 'function', 'candy': break;
        default:
          $.each(val, function(key2, val2) {
            if ((typeof val[key2]) == 'function') {
              $(document).on(key, key2, val[key2]);
            } else {
              var func = '';
              var split = '';
              if (val[key2].includes('.')) split = '.';
              else if (val[key2].includes('#')) split = '#';
              else if (val[key2].includes(' ')) split = ' ';
              func = split != '' ? val[key2].split(split) : [val[key2]];
              if (func != '') {
                var getfunc = arr;
                func.forEach(function(item) {
                  getfunc = getfunc[item] !== undefined ? getfunc[item] : getfunc[split + item];
                });
                $(document).on(key, key2, getfunc);
              }
            }
          });
      }
    });
  },

  form: function(obj, callback) {
    if(typeof obj != 'object') obj = { form: obj }
    $(obj.form).unbind('submit.candy');
    $(document).off("submit.candy", obj.form);
    $(document).on("submit.candy", obj.form, function(e){
      e.preventDefault();
      let form = $(this);
      form.find('button, input[type="button"], input[type="submit"]').prop('disabled',true);
      if(obj.messages !== false) {
        if(obj.messages == undefined || obj.messages == true || obj.messages.includes('error')) form.find('*[candy-form-error]').hide();
        if(obj.messages == undefined || obj.messages == true || obj.messages.includes('success')) form.find('*[candy-form-success]').hide();
      }
      if(form.find('input[type=file]').length > 0){
        var datastring = new FormData();
        form.find('input, select, textarea').each(function(index){
          if($(this).attr('disabled')) return false;
          if($(this).attr('type')=='file') {
            datastring.append($(this).attr('name'), $(this).prop('files')[0]);
          } else if(['checkbox','radio'].includes($(this).attr('type'))) {
            if($(this).is(':checked')) datastring.append($(this).attr('name'), $(this).val());
          } else {
            datastring.append($(this).attr('name'), $(this).val());
          }
        });
        datastring.append('token', Candy.token());
        var cache = false;
        var contentType = false;
        var processData = false;
      }else{
        var datastring = form.serialize()+'&token='+Candy.token();
        var cache = true;
        var contentType = "application/x-www-form-urlencoded; charset=UTF-8";
        var processData = true;
      }
      $.ajax({
        type: form.attr('method'),
        url: form.attr('action'),
        data: datastring,
        dataType: "json",
        contentType: contentType,
        processData: processData,
        cache: cache,
        success: function(data) {
          if(!data.success) return false
          if(obj.messages == undefined || obj.messages) {
            if(data.success.result && (obj.messages == undefined || obj.messages.includes('success') || obj.messages == true)){
              if (form.find('*[candy-form-success]').length) form.find('*[candy-form-success]').html(data.success.message).fadeIn();
              else form.append(`<span candy-form-success="${obj.form}">${data.success.message}</span>`);
            }else{
              var invalid_input_class = '_candy_error';
              var invalid_input_style = '' //'border-color:red';
              var invalid_span_class = '_candy_form_info';
              var invalid_span_style = '' //'color:red';
              let actions = Candy.candy.actions
              if(actions.candy && actions.candy.form){
                if(actions.candy.form.input){
                  if(actions.candy.form.input.class){
                    if(actions.candy.form.input.class.invalid) invalid_input_class = actions.candy.form.input.class.invalid
                  }
                  if(actions.candy.form.input.style){
                    if(actions.candy.form.input.style.invalid) invalid_input_style = actions.candy.form.input.style.invalid
                  }
                }
                if(actions.candy.form.span){
                  if(actions.candy.form.span.class){
                    if(actions.candy.form.span.class.invalid) invalid_span_class = actions.candy.form.span.class.invalid
                  }
                  if(actions.candy.form.span.style){
                    if(actions.candy.form.span.style.invalid) invalid_span_style = actions.candy.form.span.style.invalid
                  }
                }
              }
              $.each(data.errors, function(name, message) {
                if (form.find(`[candy-form-error="${name}"]`).length) form.find(`[candy-form-error="${name}"]`).html(message).fadeIn();
                else form.find('*[name="'+name+'"]').after(`<span candy-form-error="${name}" class="${invalid_span_class}" style="${invalid_span_style}">${message}</span>`);
                form.find('*[name="'+name+'"]').addClass(invalid_input_class);
                form.find('*[name="'+name+'"]').on('focus.candy', function(){
                  $(this).removeClass(invalid_input_class);
                  form.find(`[candy-form-error="${name}"]`).fadeOut();
                  form.find('*[name="'+name+'"]').unbind('focus.candy');
                })
              });
            }
          }
          if(callback!==undefined){
            if(typeof callback === "function") callback(data);
            else if(data.success.result) window.location.replace(callback);
          }
        },
        xhr: function() {
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener("progress", function(evt){
            if (evt.lengthComputable) {
              var percent = parseInt((100 / evt.total) * evt.loaded);
              if(obj.loading) obj.loading(percent);
            }
          }, false);
          return xhr;
        },
        error: function() {
          console.error('CandyJS:',"Somethings went wrong...","\nForm: "+obj.form+"\nRequest: "+form.attr('action'));
        },
        complete: function() {
          form.find('button, input[type="button"], input[type="submit"]').prop('disabled',false);
        }
      })
    })
  },

  token: function(){
    var data = Candy.data();
    if(!Candy.candy.token.listener){
      Candy.candy.token.listener = $(document).ajaxSuccess(function(res,xhr,req){
        if(req.url.substr(0,4) == 'http') return false;
        try {
          let token = xhr.getResponseHeader("X-Candy-Token");
          if(token) Candy.candy.token.hash.push(token);
          if(Candy.candy.token.hash.length > 2) Candy.candy.token.hash.shift();
        } catch (e) {
          return false;
        }
      });
    }
    if(!Candy.candy.token.hash.length){
      if(!Candy.candy.token.data && data) {
        Candy.candy.page = data.candy.page;
        Candy.candy.token.hash.push(data.candy.token);
        Candy.candy.token.data = true;
      } else {
        var req = new XMLHttpRequest();
        req.open('GET', '?_candy=token', false);
        req.setRequestHeader("X-Requested-With", "xmlhttprequest");
        req.send(null);
        var req_data = JSON.parse(req.response);
        Candy.candy.page = req_data.page;
        if(req_data.token) Candy.candy.token.hash.push(req_data.token);
      }
    }
    Candy.candy.token.hash.filter(n => n);
    var return_token = Candy.candy.token.hash.shift();
    if(!Candy.candy.token.hash.length) $.get('?_candy=token',function(data){
      var result = JSON.parse(JSON.stringify(data));
      if(result.token) Candy.candy.token.hash.push(result.token);
      Candy.candy.page = result.page;
    });
    return return_token;
  },

  page: function(){
    if(!Candy.candy.page){
      let data = Candy.data();
      if(data !== null) Candy.candy.page = data.candy.page;
      else Candy.token(true);
    }
    return Candy.candy.page;
  },

  data: function(){
    if(!document.cookie.includes('candy=')) return null;
    return JSON.parse(unescape(document.cookie.split('candy=')[1].split(';')[0]));
  },

  load: function(url,callback,push=true){
    var url_now = window.location.href;
    if(url.substr(0,4) != 'http') {
      var domain = url_now.replace('://','{:--}').split('/');
      domain[0] = domain[0].replace('{:--}','://');
      if(url.substr(0,1) == '/'){
        url = domain[0] + url
      } else {
        domain[domain.length - 1] = '';
        url = domain.join('/') + url
      }
    }
    if(url=='' || url.substring(0,11)=='javascript:' || url.includes('#')) return false;
    $.ajax({
      url: url,
      type: "GET",
      beforeSend: function(xhr){xhr.setRequestHeader('X-CANDY', 'ajaxload');xhr.setRequestHeader('X-CANDY-LOAD', Object.keys(Candy.loader.elements).join(','))},
      success: function(_data, status, request){
        if(url != url_now && push) window.history.pushState(null, document.title, url);
        Candy.candy.page = request.getResponseHeader('x-candy-page');
        $.each(Candy.loader.elements, function(index, value){
          $(value).fadeOut(400,function(){
            $(value).html(_data.output[index]);
            $(value).fadeIn();
          });
        });
        var _t = setTimeout(function(){
          if(typeof Candy.candy.actions.load == 'function') Candy.candy.actions.load(Candy.page(),_data.variables);
          if(Candy.candy.actions.page !== undefined && typeof Candy.candy.actions.page[Candy.candy.page] == "function") Candy.candy.actions.page[Candy.candy.page](Candy.data());
          if(callback!==undefined) callback(Candy.page(),_data.variables);
          $("html, body").animate({ scrollTop: 0 });
        }, 500);
      },
      error : function(){
        window.location.replace(url);
      }
    });
  },

  loader: function(element,arr,callback){
    this.loader.elements = arr;
    $(document).on('click',element,function(e){
      var url_now = window.location.href;
      var url_go = $(this).attr('href');
      var target = $(this).attr('target');
      if((target==null || target=='_self') && (url_go!='' && url_go.substring(0,11)!='javascript:' && url_go.substring(0,1)!='#') && (!url_go.includes('://') || url_now.split("/")[2]==url_go.split("/")[2])){
        e.preventDefault();
        Candy.load(url_go,callback);
      }
    });
    $(window).on('popstate', function(){
      Candy.load(window.location.href,callback,false);
    });
  }
}

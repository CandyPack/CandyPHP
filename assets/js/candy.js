var _candy_token,_candy_page,_candy_action,_candy_forms=[];class CandyJS{test(){alert("Hi, World")}showModal(n){$("#"+n).modal("show")}get(n,a){$.get(n,function(n,c){a(n,c)})}getToken(n=!1){if(n){var a=new XMLHttpRequest;a.open("GET","?_candy=token",!1),a.setRequestHeader("X-Requested-With","xmlhttprequest"),a.send(null);var c=JSON.parse(a.response);_candy_page=c.page,_candy_token=c.token}else $.get("?_candy=token",function(n){var a=JSON.parse(JSON.stringify(n));_candy_token=a.token,_candy_page=a.page})}token(){var n=candy.data();null==_candy_token&&(void 0===_candy_token&&null!==n?(_candy_page=n.candy.page,_candy_token=n.candy.token):candy.getToken(!0));var a=_candy_token;return _candy_token=null,candy.getToken(),a}page(){var n=candy.data();return null!==n?_candy_page=n.candy.page:candy.getToken(!0),_candy_page}data(){return document.cookie.includes("candy=")?JSON.parse(unescape(document.cookie.split("candy=")[1].split(";")[0])):null}form(n,a,c){if(_candy_forms.includes(n))return!1;_candy_forms.push(n),$(document).on("submit","#"+n,function(e){e.preventDefault();var t=$(this);if($("#"+n+" ._candy_form_info").remove(),$("#"+n+" ._candy").html(""),$("#"+n+" ._candy").hide(),$("#"+n+" ._candy_error").removeClass("_candy_error"),$("#"+n+" input[type=file]").length>0){var o=new FormData;$("#"+n+" input, #"+n+" select, #"+n+" textarea").each(function(n){"file"==$(this).attr("type")?o.append($(this).attr("name"),$(this).prop("files")[0]):o.append($(this).attr("name"),$(this).val())}),o.append("token",candy.token());var d=!1,i=!1,r=!1}else o=$("#"+n).serialize()+"&token="+candy.token(),d=!0,i="application/x-www-form-urlencoded; charset=UTF-8",r=!0;t.find('button, input[type="button"], input[type="submit"]').prop("disabled",!0),$.ajax({type:$("#"+n).attr("method"),url:$("#"+n).attr("action"),data:o,dataType:"json",contentType:i,processData:r,cache:d,success:function(e){if(e.success){if(void 0===c||c)if(e.success.result)$("#"+n+" ._candy_success").length?($("#"+n+" ._candy_success").show(),$("#"+n+" ._candy_success").html(e.success.message)):$("#"+n).append('<span class="_candy_form_info">'+e.success.message+"</span>");else{var t=e.errors,o="_candy_error";_candy_action.candy&&_candy_action.candy.form&&_candy_action.candy.form.input&&_candy_action.candy.form.input.class&&_candy_action.candy.form.input.class.invalid&&(o+=" "+_candy_action.candy.form.input.class.invalid);var d="_candy_form_info";_candy_action.candy&&_candy_action.candy.form&&_candy_action.candy.form.span&&_candy_action.candy.form.span.class&&_candy_action.candy.form.span.class.invalid&&(d+=" "+_candy_action.candy.form.span.class.invalid);var i="";_candy_action.candy&&_candy_action.candy.form&&_candy_action.candy.form.span&&_candy_action.candy.form.span.style&&_candy_action.candy.form.span.style.invalid?i+=" "+_candy_action.candy.form.span.style.invalid:_candy_action.candy&&_candy_action.candy.form&&_candy_action.candy.form.span&&_candy_action.candy.form.span.class&&_candy_action.candy.form.span.class.invalid||(i="color:red"),$.each(t,function(a,c){$("#"+n+" ._candy_"+a).length?($("#"+n+" ._candy_"+a).html(c),$("#"+n+" ._candy_"+a).show()):$(`#${n} [candy-form-error="${a}"], [candy-form-error="${a}"][candy-form="${n}"]`).length?($(`#${n} [candy-form-error="${a}"], [candy-form-error="${a}"][candy-form="${n}"]`).html(c),$(`#${n} [candy-form-error="${a}"], [candy-form-error="${a}"][candy-form="${n}"]`).show()):"_candy_form"==a?$("#"+n).append(`<span class="${d}" style="${i}">${c}</span>`):$("#"+n+' *[name ="'+a+'"]').after(`<span class="${d}" style="${i}">${c}</span>`),_candy_action.candy&&_candy_action.candy.form&&null!=_candy_action.candy.form.errorClear&&!_candy_action.candy.form.errorClear||$("#"+n+' *[name ="'+a+'"]').on("focus.candy",function(){$("#"+n+" ._candy_"+a).hide(),$(`#${n} [candy-form-error="${a}"], [candy-form-error="${a}"][candy-form="${n}"]`).hide(),$("#"+n+' *[name ="'+a+'"]').removeClass(o),$("#"+n+' *[name ="'+a+'"]').unbind("focus.candy").parent().find("._candy_form_info").remove()}),$("#"+n+' *[name ="'+a+'"]').addClass(o)})}void 0!==a&&("function"==typeof a?a(e):e.success.result&&window.location.replace(a))}},error:function(){console.error("Candy JS:","Somethings went wrong...","\nForm: #"+n+"\nRequest: "+$("#"+n).attr("action"))},complete:function(){t.find('button, input[type="button"], input[type="submit"]').prop("disabled",!1)}})})}loader(n,a,c){$(document).on("click",n,function(n){var e=window.location.href,t=$(this).attr("href"),o=$(this).attr("target");null!=o&&"_self"!=o||""==t||"javascript:"==t.substring(0,11)||"#"==t.substring(0,1)||t.includes("://")&&e.split("/")[2]!=t.split("/")[2]||(n.preventDefault(),_candy_action.candy&&_candy_action.candy.loader&&_candy_action.candy.loader.start&&_candy_action.candy.loader.start&&"function"==typeof _candy_action.candy.loader.start&&_candy_action.candy.loader.start(),t!=e&&window.history.pushState(null,document.title,t),$.ajax({url:t,type:"GET",beforeSend:function(n){n.setRequestHeader("X-CANDY","ajaxload"),n.setRequestHeader("X-CANDY-LOAD",Object.keys(a).join(","))},success:function(n,e,t){_candy_page=t.getResponseHeader("x-candy-page"),$.each(a,function(a,c){$(c).fadeOut(400,function(){$(c).html(n.output[a]),$(c).fadeIn()})});setTimeout(function(){void 0!==_candy_action&&"function"==typeof _candy_action.load&&_candy_action.load(candy.page(),n.variables),void 0!==_candy_action&&void 0!==_candy_action.page&&"function"==typeof _candy_action.page[_candy_page]&&_candy_action.page[_candy_page](candy.data()),void 0!==c&&c(candy.page(),n.variables),$("html, body").animate({scrollTop:0})},500)},error:function(){window.location.replace(t)}}))}),$(window).on("popstate",function(){var n=window.location.href;""==n||"javascript:"==n.substring(0,11)||n.includes("#")||$.each(a,function(n,e){$.ajax({url:window.location.href,type:"GET",beforeSend:function(n){n.setRequestHeader("X-CANDY","ajaxload"),n.setRequestHeader("X-CANDY-LOAD",Object.keys(a).join(","))},success:function(n,e,t){_candy_page=t.getResponseHeader("x-candy-page"),$.each(a,function(a,c){$(c).fadeOut(400,function(){$(c).html(n.output[a]),$(c).fadeIn()})});setTimeout(function(){void 0!==_candy_action&&"function"==typeof _candy_action.load&&_candy_action.load(),void 0!==_candy_action&&void 0!==_candy_action.page&&"function"==typeof _candy_action.page[_candy_page]&&_candy_action.page[_candy_page](),void 0!==c&&c(candy.page(),n.variables)},500)},error:function(){window.location.replace(window.location.href)}})})})}action(n){if("object"!=typeof n)return _candy_action;_candy_action=n,$.each(n,function(a,c){switch(a){case"load":$(function(){c()});break;case"page":$.each(c,function(n,a){n==candy.page()&&$(function(){a(candy.data())})});break;case"start":$(function(){c()});break;case"interval":$.each(c,function(n,a){$(function(){setInterval(function(){a()},n)})});break;case"function":case"candy":break;default:$.each(c,function(e,t){if("function"==typeof c[e])$(document).on(a,e,c[e]);else{var o="",d="";if(c[e].includes(".")?d=".":c[e].includes("#")?d="#":c[e].includes(" ")&&(d=" "),""!=(o=""!=d?c[e].split(d):[c[e]])){var i=n;o.forEach(function(n){i=void 0!==i[n]?i[n]:i[d+n]}),$(document).on(a,e,i)}}})}})}}var candy=new CandyJS;
// - DEPRECATED

const Candy = {
  candy: {
    token: null,
    actions: {}
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
    $(document).on("submit.candy", obj.form, function(e){
      e.preventDefault();
      let form = $(this);
      form.find('button, input[type="button"], input[type="submit"]').prop('disabled',true);
      if(obj.messages == undefined || obj.messages == true || obj.messages.includes('error')) form.find('*[candy-form-error]').hide();
      if(obj.messages == undefined || obj.messages == true || obj.messages.includes('success')) form.find('*[candy-form-success]').hide();
      if(form.find('input[type=file]').length > 0){
        var datastring = new FormData();
        form.find('input, select, textarea').each(function(index){
          if($(this).attr('type')=='file') datastring.append($(this).attr('name'), $(this).prop('files')[0]);
          else datastring.append($(this).attr('name'), $(this).val());
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
            if(data.success.result && (obj.messages == true || obj.messages.includes('success'))){
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
        error: function() {
          console.error('CandyJS:',"Somethings went wrong...","\nForm: #"+id+"\nRequest: "+$("#"+id).attr('action'));
        },
        complete: function() {
          form.find('button, input[type="button"], input[type="submit"]').prop('disabled',false);
        }
      })
    })
  },

  token: function(){
    var data = Candy.data();
    if(Candy.candy.token === undefined || Candy.candy.token === null){
      if(Candy.candy.token === undefined && data !== null) {
        _candy_page = data.candy.page;
        Candy.candy.token = data.candy.token;
      } else {
        var req = new XMLHttpRequest();
        req.open('GET', '?_candy=token', false);
        req.setRequestHeader("X-Requested-With", "xmlhttprequest");
        req.send(null);
        var req_data = JSON.parse(req.response);
        _candy_page = req_data.page;
        Candy.candy.token = req_data.token;
      }
    }
    var return_token = Candy.candy.token;
    Candy.candy.token = null;
    $.get('?_candy=token',function(data){
      var result = JSON.parse(JSON.stringify(data));
      Candy.candy.token = result.token;
      _candy_page = result.page;
    });
    return return_token;
  },

  page: function(){
    let data = candy.data();
    if(data !== null) _candy_page = data.candy.page;
    else Candy.getToken(true);
    return _candy_page;
  },

  data: function(){
    if(!document.cookie.includes('candy=')) return null;
    return JSON.parse(unescape(document.cookie.split('candy=')[1].split(';')[0]));
  }
}

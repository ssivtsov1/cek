function showMessage(){
    alert("Зачем кликать по логотипу?");
}

function value_option(){
var form = document.generalform;
var select = form.elements.option;
for (var i = 0; i < select.options.length; i++) {
  var option = select.options[i];
  if(option.selected) {
  var select_opt = option.value;
  }
}
  return select_opt;
}    

function set_selected_index(value_opt){
    var sel = document.getElementById('selectid');
    var opts = sel.options;
    for(var opt, j = 0; opt = opts[j]; j++) {
        if(opt.value == value_opt) {
            sel.selectedIndex = j;
            break;
        }
    }
}

function reload_header(){
    var message = value_option();
    document.location.href="index.php?message="+message;
    }

function reload_subdivision(subdev){
    var obj = eval(subdev.toString());
    document.generalform.option_podr.options[0] = new Option("Все подразделения", "%", true);     
    for(i=1;i<obj.length+1;i++){
        document.generalform.option_podr.options[i] = new Option(obj[i-1], obj[i-1], true);
        document.generalform.option_podr.selectedIndex = 0;
    }
}

function java_to_php(){
//var message = value_option();
//alert("Выбрали"+value_option());
$.get('index.php',reload_header());
//$.get('index.php?message='+message, reload_subdivision());
//reload_subdivision();
//location.reload();
//$.get('index.php', {message:message}, reload_subdivision());
}
$(document).ready(function() {
    
  $('[data-toggle=offcanvas]').click(function() {
    $('.row-offcanvas').toggleClass('active');
  });
  
});

function setTarget(){
   var select = document.getElementById('target');
   var target_value = select.options[select.selectedIndex].value;
   document.mysearch.action = target_value;
   mysearch.submit();
}
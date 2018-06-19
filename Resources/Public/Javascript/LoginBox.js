// @todo refactor so we don't have to deal how to add events
function t3UsersAddEvent(obj, type, fn) {
    if (obj.addEventListener) {
        obj.addEventListener(type, fn, false);
    } else if (obj.attachEvent) {
        obj.attachEvent('on' + type, function() { return fn.apply(obj, [window.event]);});
    }
}
t3UsersAddEvent(window, 'load', function(){
    // sync permalogin checkbox with the actual hidden field
    t3UsersAddEvent(
         document.getElementById('permalogin_checkbox'),
         'click',
         function(e){
             document.getElementById('permaloginHiddenField').value = this.checked ? 1 : 0;
         }
     );
});

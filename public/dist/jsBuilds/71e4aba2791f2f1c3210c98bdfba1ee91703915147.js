"use strict";$(function(){$('[data-toggle="tooltip"]').tooltip();});var Installer={togglePasswordField:function(field='password'){if($('#'+field).attr('type')==='password'){$('#'+field).attr('type','text');$('.hide-pass').removeClass('d-none');$('.show-pass').addClass('d-none');$('.h-pill').attr('data-original-title',trans('Hide password')).tooltip('update').tooltip('show');}
else{$('#'+field).attr('type','password');$('.show-pass').removeClass('d-none');$('.hide-pass').addClass('d-none');$('.h-pill').attr('data-original-title',trans('Show password')).tooltip('update').tooltip('show');}}};
$(function () {
    'use strict';

    function init() {
        $('#pass').attr('oninput', 'validateStrongPassword(this.value)');
    }

    init();
});

function validateStrongPassword (value = '') {
    let passReg = new RegExp('(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{10,})');
    if(!passReg.test(value)) {
        $('#passwordComplexError').html('Requires 1 Number, 1 Symbol, 1 Capital Letter, 1 Lowercase Letter, and 10 Characters Minimum');
        $('#SubmitRegisterUserForm').prop('disabled', true);
    } else {
        $('#SubmitRegisterUserForm').prop('disabled', false);
        $('#passwordComplexError').html('');
    }
}
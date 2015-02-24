describe('dynamic fields', function() {
    beforeEach(function() {
        $([
            '<form class="form-horizontal" id="dynamicForm">',
                '<div class="form-group">',
                    '<input type="text" name="fullName" class="form-control" />',
                '</div>',
            '</form>'
        ].join('\n')).appendTo('body');

        $('#dynamicForm').formValidation({
            fields: {
                fullName: {
                    validators: {
                        notEmpty: {
                            message: 'The full name is required and cannot be empty'
                        },
                        stringLength: {
                            min: 8,
                            max: 40,
                            message: 'The full name must be more than %s and less than %s characters long'
                        },
                        regexp: {
                            enabled: false,
                            regexp: /^[a-zA-Z\s]+$/,
                            message: 'The full name can only consist of alphabetical, number, and space'
                        }
                    }
                },
                // #725: Note that the email field isn't available in the form yet
                email: {
                    validators: {
                        emailAddress: {
                            message: 'The email address is not valid'
                        }
                    }
                }
            }
        });

        this.fv        = $('#dynamicForm').data('formValidation');
        this.$fullName = this.fv.getFieldElements('fullName');
    });

    afterEach(function() {
        $('#dynamicForm').formValidation('destroy').remove();
    });

    // https://github.com/formvalidation/formvalidation/pull/725
    it('adding field [does not exist but is already set in "fields" option]', function() {
        var $div   = $('<div/>').addClass('form-group').appendTo($('#dynamicForm'));
            $email = $('<input/>')
                        .attr('type', 'text')
                        .addClass('form-control')
                        .attr('name', 'email')
                        .appendTo($div);

        this.fv.addField('email');

        this.$fullName.val('Phuoc Nguyen');

        $email.val('not valid@email');
        this.fv.validate();
        expect(this.fv.isValidField('email')).toBeFalsy();
        expect(this.fv.isValid()).toBeFalsy();

        this.fv.resetForm();
        $email.val('valid@email.com');
        this.fv.validate();
        expect(this.fv.isValidField('email')).toBeTruthy();
        expect(this.fv.isValid()).toBeTruthy();
    });
});

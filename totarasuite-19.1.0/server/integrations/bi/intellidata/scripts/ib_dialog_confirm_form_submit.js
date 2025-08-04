/* A confirmation dialog to be displayed when a button is clicked in the UI.
It will display a question, Yes/No buttons and submit the form that matches the form_id passed in.
 */

var ib_dialog_confirm_form_submit =  {
    init: function(question, aurl, form_to_submit) {
        Y.use('moodle-core-notification-confirm', function() {
            var confirm = new M.core.confirm({
                title: M.util.get_string('confirm', 'moodle'),
                question: question,
                yesLabel: 'Yes',
                noLabel: 'No'
            });
            confirm.on('complete-yes', function () {
                confirm.hide();
                confirm.destroy();
                var form_tbl = document.getElementById(form_to_submit);
                form_tbl.action = aurl;
                form_tbl.submit();
            }, self);
            confirm.show();
        });
    }
};

YUI.add('moodle-availability_audience-form', function (Y, NAME) {

/**
 * JavaScript for form editing cohort member conditions.
 *
 * @module moodle-availability_audience-form
 */
M.availability_audience = M.availability_audience || {};

/**
 * @class M.availability_audience.form
 * @extends M.core_availability.plugin
 */
M.availability_audience.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} initParams Array of objects containing extra data for the form
 */
M.availability_audience.form.initInner = function(initParams) {
    this.dialogConfig = initParams;

};

/**
 * Generate an index so we can identify separate
 * audience conditions.
 */
M.availability_audience.form.generateIndex = (function() {
    var _count = 0;
    return function() {
        return _count++;
    };
})();

M.availability_audience.form.getNode = function(json) {

    // Increment number used for unique ids.
    const index = M.availability_audience.form.generateIndex();
    const selectName = 'cohort[' + index + ']';
    const hiddenName = 'cohortid[' + index + ']';
    let cohort = json.cohort === undefined ? 0 : json.cohort;
    const originalCohort = cohort;
    const selectSelector = '[name="' + selectName + '"]';
    const hiddenSelector = '[name="' + hiddenName + '"]';

    // Create HTML structure.
    let html = '<label class="form-group" for="avail-audience"><span class="pr-3">' + M.util.get_string('title', 'availability_audience') + '</span> ';
    html += '<span class="availability-group">';
    html += '</label>';
    html += '<select id="avail-audience" name="' + selectName + '">';
    if (cohort > 0) {
        if (typeof this.dialogConfig !== 'undefined') {
            if (typeof this.dialogConfig.audienceNames[cohort] !== 'undefined') {
                html += '<option value=' + cohort + '>' + this.dialogConfig.audienceNames[cohort].name + '</option>';
            } else {
                cohort = 0;
            }
        }
    }
    html += '</select>';
    html += '<input type="hidden" name="' + hiddenName + '" value="' + cohort + '" />';
    html += '</span>';

    const node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    const initautocomplete = function() {
        // Due to issues caused by race conditions we must use AMD here
        // so we can be sure that the form was added to the DOM.
        require(['core/form-autocomplete', 'jquery'], function(autoComplete, $) {

            autoComplete.enhance(selectSelector, false, 'availability_audience/ajax_handler', 'Type something');

            $(selectSelector).on('change', function(evt) {
                // Triggers the value being updated.
                $(hiddenSelector).val($(selectSelector).val());
                M.core_availability.form.update();
            });
        });
    };

    if (originalCohort > 0 && typeof this.dialogConfig !== 'undefined' && typeof this.dialogConfig.audienceNames[originalCohort] === 'undefined') {
        const audienceNames = this.dialogConfig.audienceNames;
        require(['jquery'], function($) {
            // Audience name missing, fetch it
            const query = $.ajax({
                url: M.cfg.wwwroot + '/availability/condition/audience/ajax.php',
                type: 'POST',
                data: {
                    cohortid: originalCohort
                }
            });
            query.done(function(result) {
                if (result.length) {
                    cohort = originalCohort;
                    audienceNames[cohort] = {id: cohort, fullname: result[0].label};
                    const option = $('<option>');
                    option.append(result[0].label);
                    option.attr('value', cohort);
                    option.prop('selected', true);
                    $(selectSelector).append(option);
                    $(selectSelector).val(cohort);
                    $(hiddenSelector).val(cohort);
                }
                initautocomplete();
                M.core_availability.form.update();
            }).fail(function() {
                initautocomplete();
            });
        });
    } else {
        initautocomplete();
    }
    return node;
};

M.availability_audience.form.fillValue = function(value, node) {
    value.cohort = node.one('[name^=cohortid]').getAttribute('value');
};

M.availability_audience.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check a audience has been set.
    if (value.cohort.trim() === '0') {
        errors.push('availability_audience:error_selectfield');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});

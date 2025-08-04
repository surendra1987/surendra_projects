YUI.add('moodle-availability_hierarchy_organisation-form', function (Y, NAME) {

/**
 * JavaScript for form editing organisation conditions.
 *
 * @module moodle-availability_hierarchy_organisation-form
 */
M.availability_hierarchy_organisation = M.availability_hierarchy_organisation || {};

/**
 * @class M.availability_hierarchy_organisation.form
 * @extends M.core_availability.plugin
 */
M.availability_hierarchy_organisation.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} initParams Array of objects extra info for the form key => value
 */
M.availability_hierarchy_organisation.form.initInner = function(initParams) {
    this.conditionConfig = initParams;

};

/**
 * Generate an index so we can identify separate
 * organisation conditions.
 */
M.availability_hierarchy_organisation.form.generateIndex = (function() {
    var _count = 0;
    return function() {
        return _count++;
    };
})();


M.availability_hierarchy_organisation.form.getNode = function(json) {
    // Increment number used for unique ids.
    const index = M.availability_hierarchy_organisation.form.generateIndex();

    const selectName = 'org[' + index + ']';
    const hiddenName = 'orgid[' + index + ']';
    let organisation = json.organisation === undefined ? 0 : json.organisation;
    const originalOrganisation = organisation;
    const selectSelector = '[name="' + selectName + '"]';
    const hiddenSelector = '[name="' + hiddenName + '"]';

    // Create HTML structure.
    let html = '<label class="form-group" for="avail-organisation"><span class="pr-3">' + M.util.get_string('title', 'availability_hierarchy_organisation') + '</span> ';
    html += '<span class="availability-group">';
    html += '</label>';
    html += '<select id="avail-organisation" name="' + selectName + '">';
    if (organisation > 0) {
        if (typeof this.conditionConfig !== 'undefined') {
            if (typeof this.conditionConfig.organisationNames[organisation] !== 'undefined') {
                html += '<option value=' + organisation + '>' + this.conditionConfig.organisationNames[organisation].fullname + '</option>';
            } else {
                organisation = 0;
            }
        }
    }
    html += '</select>';
    html += '<input type="hidden" name="' + hiddenName + '" value="' + organisation + '" />';
    html += '</span>';

    const node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    const initautocomplete = function() {
        // Due to issues caused by race conditions we must use AMD here
        // so we can be sure that the form was added to the DOM.
        require(['core/form-autocomplete', 'jquery'], function(autoComplete, $) {
            autoComplete.enhance(selectSelector, false, 'availability_hierarchy_organisation/ajax_handler', M.util.get_string('searchorganisations', 'availability_hierarchy_organisation'));

            $(selectSelector).on('change', function(evt) {
                // Triggers the value being updated.
                $(hiddenSelector).val($(selectSelector).val());
                M.core_availability.form.update();
            });
        });
    };

    if (originalOrganisation > 0 && typeof this.conditionConfig !== 'undefined' && typeof this.conditionConfig.organisationNames[originalOrganisation] === 'undefined') {
        const organisationNames = this.conditionConfig.organisationNames;
        require(['jquery'], function($) {
            // Organisation name missing, fetch it
            const query = $.ajax({
                url: M.cfg.wwwroot + '/availability/condition/hierarchy_organisation/ajax.php',
                type: 'POST',
                data: {
                    organisationid: originalOrganisation
                }
            });
            query.done(function(result) {
                if (result.length) {
                    organisation = originalOrganisation;
                    organisationNames[organisation] = {id: organisation, fullname: result[0].label};
                    const option = $('<option>');
                    option.append(result[0].label);
                    option.attr('value', organisation);
                    option.prop('selected', true);
                    $(selectSelector).append(option);
                    $(selectSelector).val(organisation);
                    $(hiddenSelector).val(organisation);
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

M.availability_hierarchy_organisation.form.fillValue = function(value, node) {
    value.organisation = node.one('[name^=orgid]').getAttribute('value');
};

M.availability_hierarchy_organisation.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check an organisation has been set.
    if (value.organisation.trim() === '0') {
        errors.push('availability_hierarchy_organisation:error_selectfield');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});

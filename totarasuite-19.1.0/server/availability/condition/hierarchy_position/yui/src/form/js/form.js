/**
 * JavaScript for form editing position conditions.
 *
 * @module moodle-availability_hierarchy_position-form
 */
M.availability_hierarchy_position = M.availability_hierarchy_position || {};

/**
 * @class M.availability_hierarchy_position.form
 * @extends M.core_availability.plugin
 */
M.availability_hierarchy_position.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} initParams Array of objects extra info for the form key => value
 */
M.availability_hierarchy_position.form.initInner = function(initParams) {
    this.conditionConfig = initParams;

};

/**
 * Generate an index so we can identify separate
 * position conditions.
 */
M.availability_hierarchy_position.form.generateIndex = (function() {
    var _count = 0;
    return function() {
        return _count++;
    };
})();


M.availability_hierarchy_position.form.getNode = function(json) {
    // Increment number used for unique ids.
    const index = M.availability_hierarchy_position.form.generateIndex();

    const selectName = 'pos[' + index + ']';
    const hiddenName = 'posid[' + index + ']';
    let position = json.position === undefined ? 0 : json.position;
    const originalPosition = position;
    const selectSelector = '[name="' + selectName + '"]';
    const hiddenSelector = '[name="' + hiddenName + '"]';

    // Create HTML structure.
    let html = '<label class="form-group" for="avail-position"><span class="pr-3">' + M.util.get_string('title', 'availability_hierarchy_position') + '</span> ';
    html += '<span class="availability-group">';
    html += '</label>';
    html += '<select id="avail-position" name="' + selectName + '">';
    if (position > 0) {
        if (typeof this.conditionConfig !== 'undefined') {
            if (typeof this.conditionConfig.positionNames[position] !== 'undefined') {
                html += '<option value=' + position + '>' + this.conditionConfig.positionNames[position].fullname + '</option>';
            } else {
                position = 0;
            }
        }
    }
    html += '</select>';
    html += '<input type="hidden" name="' + hiddenName + '" value="' + position + '" />';
    html += '</span>';

    const node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    const initautocomplete = function() {
        // Due to issues caused by race conditions we must use AMD here
        // so we can be sure that the form was added to the DOM.
        require(['core/form-autocomplete', 'jquery'], function(autoComplete, $) {
            autoComplete.enhance(selectSelector, false, 'availability_hierarchy_position/ajax_handler', M.util.get_string('searchpositions', 'availability_hierarchy_position'));

            $(selectSelector).on('change', function(evt) {
                // Triggers the value being updated.
                $(hiddenSelector).val($(selectSelector).val());
                M.core_availability.form.update();
            });
        });
    };

    if (originalPosition > 0 && typeof this.conditionConfig !== 'undefined' && typeof this.conditionConfig.positionNames[originalPosition] === 'undefined') {
        const positionNames = this.conditionConfig.positionNames;
        require(['jquery'], function($) {
            // Position name missing, fetch it
            const query = $.ajax({
                url: M.cfg.wwwroot + '/availability/condition/hierarchy_position/ajax.php',
                type: 'POST',
                data: {
                    positionid: originalPosition
                }
            });
            query.done(function(result) {
                if (result.length) {
                    position = originalPosition;
                    positionNames[position] = {id: position, fullname: result[0].label};
                    const option = $('<option>');
                    option.append(result[0].label);
                    option.attr('value', position);
                    option.prop('selected', true);
                    $(selectSelector).append(option);
                    $(selectSelector).val(position);
                    $(hiddenSelector).val(position);
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

M.availability_hierarchy_position.form.fillValue = function(value, node) {
    value.position = node.one('[name^=posid]').getAttribute('value');
};

M.availability_hierarchy_position.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check an position has been set.
    if (value.position.trim() === '0') {
        errors.push('availability_hierarchy_position:error_selectfield');
    }
};

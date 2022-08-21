/**
 * PSA - Pre-Semester Assignment <br>
 * All JS functions used in the CSP site
 * @author  JDel
 */
let PSA = {
    /**
     * All Functions Relating to Opportunities
     */
    Opportunity: {
        properties: {
            add: {
                'Name':     { name: 'name',      type: 'input'  },
                'Date':     { name: 'date',      type: 'input'  },
                'Center':   { name: 'center',    type: 'select' },
            },
            edit: {
                'Name':         { name: 'name',       type: 'input' },
                'Date':         { name: 'date',       type: 'input' },
                'Center':       { name: 'center',     type: 'select'},
                'Volunteers':   { name: 'volunteers', type: 'input' },
            },
        },

        /**
         * Initializers for Forms/Pages
         */
        init: {
            add: function () {
                // Make Sure Validation Is Reset
                PSA.Volunteer.resetValidation();
                // Add Submit Action
                $('#SubmitAddOpportunity').on('click', () => { PSA.Opportunity.add(); });
            },
            edit: function (id) {
                // Make Sure Validation Is Reset
                PSA.Volunteer.resetValidation();
                // Add Volunteer Action
                $('#addVolunteerBtn').on('click', () => { PSA.Opportunity.addVolunteer(id); });
                // Add Submit Action
                $('#SubmitEditOpportunity').on('click', () => { PSA.Opportunity.edit(id); });
            },
        },

        /**
         * Get the Current Selected Volunteers in Array Format
         *
         * Gets the Selected Volunteers from the input field
         * @return {Array}
         */
        getCurVolunteerArray: function () {
            let loc = PSA.Opportunity.properties.edit.Volunteers;
            return $(`${loc.type}[name="${loc.name}"]`).val().split(',');
        },

        /**
         * Gets the Volunteers Options for the Opportunity then adds them to the edit page
         * @param id {Number}   ID of the Opportunity to add to
         */
        addVolunteer: function (id) {
            // Step 2: Get Selected Volunteer
            let successAction = async (volunteers) => {
                let options = {};
                if(volunteers.length > 0 && volunteers[0].first_name) {
                    volunteers.forEach(({first_name, last_name, email, id}) => {
                        // First Check If Already Added (Prevent Duplicates)
                        let cur = PSA.Opportunity.getCurVolunteerArray();
                        let found = false;
                        cur.forEach(sel_id => { found = (found) ? found : (sel_id == id); })
                        if(!found) options[`${id}`] = `(${id}) ${first_name} ${last_name} - ${email}`;
                    });
                } else { options[''] = `No Matches Found`; }
                if(Object.keys(options).length === 0) options[''] = `No Matches Found`;

                const { value: volunteer } = await Swal.fire({
                    title: 'Select A Volunteer to Add',
                    input: 'select',
                    inputOptions: options,
                    inputPlaceholder: 'Select a Volunteer',
                    showCancelButton: true,
                });

                // Step 3: Save Selected Volunteer
                if (volunteer) {
                    let selected = volunteers.find(({id}) => id === volunteer);

                    // Add Data for Submit
                    let loc = PSA.Opportunity.properties.edit.Volunteers;
                    let field = $(`${loc.type}[name="${loc.name}"]`);
                    field.val(`${field.val()}${selected.id},`);

                    // Add Data to Visual
                    let html = `<tr id="${selected.id}Volunteer">
                                    <td>${selected.id}</td>
                                    <td>${selected.username}</td>
                                    <td>${selected.first_name}</td>
                                    <td>${selected.last_name}</td>
                                    <td>${selected.email}</td>
                                    <td><i class="fa fa-trash link-danger c-pointer" onclick="PSA.Opportunity.removeVolunteer(${selected.id})"></i></td>
                                </tr>`;
                    $('#addVolunteerRow').before(html);
                }
            }
            // Step 1: Get Volunteer Options
            PSA.Helpers.ajax('loadOpportunityOptions', {id: id}, successAction, 'Load Volunteer Options');
        },

        /**
         * Remove Volunteer from Edit Page
         * @param id {Number}   ID of the Volunteer
         */
        removeVolunteer: function (id) {
            // Remove From Data
            let loc = PSA.Opportunity.properties.edit.Volunteers;
            let field = $(`${loc.type}[name="${loc.name}"]`);
            let val = field.val();
            val = val.replaceAll(`${id},`, '');
            field.val(`${val}`);

            // Remove From Visuals
            $(`#${id}Volunteer`).remove();
        },

        /**
         * Submit Add Opportunity Form
         */
        add: function () {
            // Step 1: Reset Validation
            PSA.Opportunity.resetValidation();

            // Step 2: Get Data
            let data = {};
            // Dynamically Add Data to Storage Variable
            $.each(PSA.Opportunity.properties.add, (key, value) => data[value.name] = $(`${value.type}[name="${value.name}"]`).val());

            // Step 3: Setup Actions
            // Validation Action
            let validateAction = (errs) => {
                if(typeof errs === 'string') data = JSON.parse(errs);
                console.log('Validation Data: ' + JSON.stringify(errs));
                errs.forEach(({id, data}) => $(`#${id}_validation`).html(data).show());
            };
            // Success Action
            let successAction = (info) => {
                PSA.Helpers.Swal.Redirect.success('Opportunity Creation Succeeded', window.location.origin + '/dashboard/opportunities');
            };

            // Step 4: Submit Form
            PSA.Helpers.ajax('AddOpportunity', data, successAction, 'Opportunity Creation', true, false, true, validateAction);
        },

        /**
         * Reset Form Validation
         */
        resetValidation: function () {
            $('.invalid-feedback').hide();
        },

        /**
         * Submit the Edit Opportunity Form
         * @param id
         */
        edit: function (id) {
            // Step 1: Reset Validation
            PSA.Opportunity.resetValidation();

            // Step 2: Get Data
            let data = {id: id};
            // Dynamically Add Data to Storage Variable
            $.each(PSA.Opportunity.properties.edit, (key, value) => data[value.name] = $(`${value.type}[name="${value.name}"]`).val());

            // Step 3: Setup Actions
            // Validation Action
            let validateAction = (errs) => {
                if(typeof errs === 'string') data = JSON.parse(errs);
                errs.forEach(({id, data}) => $(`#${id}_validation`).html(data).show());
            };
            // Success Action
            let successAction = (info) => {
                PSA.Helpers.Swal.Redirect.success('Opportunity Modification Succeeded', '/dashboard/opportunities');
            };

            // Step 4: Submit Form
            PSA.Helpers.ajax('EditOpportunity', data, successAction, 'Opportunity Modification', true, false, true, validateAction);
        },

        /**
         * Deletes a Opportunity When Confirmed
         * @param id    {Number}    Opportunity ID
         */
        delete: function (id) {
            // Step 1: Build Variables
            // Action on Delete Success
            let successAction = () => {
                PSA.Helpers.Swal.Reload.success(`Opportunity ${id} Successfully Deleted`);
            };
            // Action on Delete Confirm
            let confirmAction = () => {
                PSA.Helpers.ajax('DeleteOpportunity', {id: id}, successAction, 'Opportunity Deletion');
            };

            // Step 2: Execute Call
            PSA.Helpers.Swal.Confirmation.delete(`Opportunity ${id}`, confirmAction);
        },
    },

    /**
     * All Functions Relating to Volunteers
     */
    Volunteer: {
        /**
         * Names for Inputs <br>
         * Serializes names for cleaner functions
         */
        properties: {
            'First Name':           { name: 'first_name',   type: 'input'       },
            'Last Name':            { name: 'last_name',    type: 'input'       },
            'Username':             { name: 'username',     type: 'input'       },
            'Email':                { name: 'email',        type: 'input'       },
            'Password':             { name: 'password',     type: 'input'       },
            'Confirm Password':     { name: 'pass_confirm', type: 'input'       },
            'Address':              { name: 'address',      type: 'input'       },
            'Home Phone':           { name: 'home',         type: 'input'       },
            'Work Phone':           { name: 'work',         type: 'input'       },
            'Cell Phone':           { name: 'cell',         type: 'input'       },
            'Centers':              { name: 'centers',      type: 'input'       },
            'Skills':               { name: 'skills',       type: 'textarea'    },
            'Available':            { name: 'available',    type: 'textarea'    },
            'Licenses':             { name: 'licenses',     type: 'textarea'    },
            'Drivers License':      { name: 'drivers',      type: 'input'       },
            'Social Card':          { name: 'social',       type: 'input'       },
            'Education Background': { name: 'background',   type: 'textarea'    },
            'Emergency Name':       { name: 'e_name',       type: 'input'       },
            'Emergency Phone':      { name: 'e_phone',      type: 'input'       },
            'Emergency Email':      { name: 'e_email',      type: 'input'       },
            'Emergency Address':    { name: 'e_address',    type: 'input'       },
        },

        /**
         * Get Property (By Name)
         * @param name  string Name
         */
        getProperty (name) {
            $.each(PSA.Volunteer.properties, (key, value) => { if(value['name'] === name) return value; })
        },

        /**
         * Add Init Functions Per Page/Form
         */
        init: {
            add: function (postData) {
                // Make Sure Validation Is Reset
                PSA.Volunteer.resetValidation();
                // Add Center Functionality to Centers Area
                $('select[name="centersList"]').on('change', () => { PSA.Volunteer.addCenter(); });
                // Display Any Current Center Selection
                let arr = $('input[name="centers"]').val().split(',');
                if(arr.length > 0) {
                    arr.filter(n => n).forEach(c => {
                        let centerName = $(`select[name='centersList'] option[value=${c}]`).html();
                        PSA.Volunteer.amendCenter(c, centerName.substr(0, centerName.indexOf(' - ')));
                    });
                }

                // Check and Add Post Data
                if(postData) {
                    if(postData['Success']) {
                        PSA.Helpers.Swal.Redirect.success('Volunteer Creation Succeeded', window.location.origin + '/dashboard/volunteers');
                    } else if(!postData['valid']) {
                        // Generate Validation Information
                        postData['errorsData'].forEach(({id, data}) => $(`#${id}_validation`).html(data).show());
                    } else {
                        // Note And Alert User of Error(s)
                        PSA.Debug.add('Volunteer Creation Failed', 'Volunteer Init Add', postData);
                        PSA.Helpers.Swal.error('Volunteer Creation Failed at DB');
                    }
                }
            },
            edit: function (postData) {
                // Make Sure Validation Is Reset
                PSA.Volunteer.resetValidation();
                // Add Center Functionality to Centers Area
                $('select[name="centersList"]').on('change', () => { PSA.Volunteer.addCenter(); });
                // Display Any Current Center Selection
                let arr = $('input[name="centers"]').val().split(',');
                if(arr.length > 0) {
                    arr.filter(n => n).forEach(c => {
                        let centerName = $(`select[name='centersList'] option[value=${c}]`).html();
                        PSA.Volunteer.amendCenter(c, centerName.substr(0, centerName.indexOf(' - ')));
                    });
                }

                // Check and Add Post Data
                if(postData) {
                    if(postData['Success']) {
                        PSA.Helpers.Swal.Redirect.success('Volunteer Modification Succeeded', window.location.origin + '/dashboard/volunteers');
                    } else if(!postData['valid']) {
                        // Generate Validation Information
                        postData['errorsData'].forEach(({id, data}) => $(`#${id}_validation`).html(data).show());
                    } else {
                        // Note And Alert User of Error(s)
                        PSA.Debug.add('Volunteer Modification Failed', 'Volunteer Init Modification', postData);
                        PSA.Helpers.Swal.error('Volunteer Modification Failed at DB');
                    }
                }
            },
        },

        resetValidation() {
            $('.invalid-feedback').hide();
        },

        /**
         * Add a Center to Form
         */
        addCenter: function () {
            // Step 1: Get Data from Input
            let center = parseInt($('select[name="centersList"]').val());

            // If Valid -> Continue
            if(center > 0) {
                // Step 2: Add to Input
                let curVal = $('input[name="centers"]').val(`${$('input[name="centers"]').val()}${center},`);

                // Step 3: Display Selection
                let centerName = $(`select[name='centersList'] option[value=${center}]`).html();
                centerName = centerName.substr(0, centerName.indexOf(' - '));
                PSA.Volunteer.amendCenter(center, centerName);

                // Step 4: Reset Select
                $(`select[name="centersList"] option[value="${center}"]`).hide()
                $('select[name="centersList"] option:first').prop('selected', true);
            }
        },

        /**
         * Amends Center to Visual Centers
         * @param id    {Number}    ID of the Center
         * @param name  {string}    Name of the Center
         */
        amendCenter: function (id, name) {
            $('#selectedCenters').append(`<span id="centerSelected${id}" class="badge rounded-pill text-bg-secondary">
                                            ${name} <span title="Remove" class="c-pointer" onclick="PSA.Volunteer.removeCenter(${id})">X</span>
                                        </span>`);
        },

        /**
         * Remove Center <br>
         * Remove Center from Form
         * @param id    {Number}     Id of Center to remove
         */
        removeCenter: function (id) {
            if(id > 0) {
                // Remove From Top Section
                $(`#centerSelected${id}`).remove();
                $(`select[name="centersList"] option[value="${id}"]`).show()

                // Remove Value from input
                $('input[name="centers"]').val($('input[name="centers"]').val().replaceAll(`${id},`,''));
            }
        },

        /**
         * Deletes a Volunteer When Confirmed
         * @param id    {Number}    Volunteer ID
         */
        delete: function (id) {
            // Step 1: Build Variables
            // Action on Delete Success
            let successAction = () => {
                PSA.Helpers.Swal.success(`Volunteer ${id} Successfully Deleted`);
            };
            // Action on Delete Confirm
            let confirmAction = () => {
                PSA.Helpers.ajax('DeleteVolunteer', {id: id}, successAction, 'Volunteer Deletion', false);
            };

            // Step 2: Execute Call
            PSA.Helpers.Swal.Confirmation.delete(`Volunteer ${id}`, confirmAction);
        }
    },

    /**
     * Audit Log Funcitons <br>
     * For everything on the Audit Dashboard
     */
    Audit: {
        init: function () {
            $("#AuditPageSelect").on('change', function() {
                window.location.href = `?page=${$(this).val()}`;
            });
        }
    },

    /**
     * Test Functions <br>
     * Delete during/after development
     */
    Test: {
        init: function () {
            // Step 1: Build Variables
            // Action on Success
            let successAction = (data) => {
                $('#output').html(JSON.stringify(data));

                // Setup Entity
                $('select[name=entity]').html(PSA.Test.buildSelect(data.Entities));

                // Setup Actions
                $('select[name=action]').html(PSA.Test.buildSelect(data.Actions));
            };

            // Step 2: Execute Call
            PSA.Helpers.ajax('getActions', null, successAction, 'Load Data');
        },

        /**
         *
         * @param options   {Object}     Op
         * @param selected  {String|boolean}
         */
        buildSelect: function (options, selected = false) {
            let output = '';
            let sel = (val) => (val === selected) ? ' selected' : '';
            Object.entries(options).forEach(([option, value]) => output += `<option value="${value}"${sel(value)}>${option}</option>`);
            return output;
        },

        checkPermission: function () {
            // Step 1: Build Variables
            // Data to Submit
            let data = {
                entity: $('select[name=entity]').val() ?? 'volunteer',
                action: $('select[name=action]').val() ?? 'view',
                user: $('select[name=user]').val() ?? 0,
            };
            // Success Action
            let successAction = (data) => {
                $('#content').html(JSON.stringify(data));
                Swal.fire('Done', "" + data);
            };

            // Step 2: Execute Call
            PSA.Helpers.ajax('CheckPermission', data, successAction, 'Check Permission');
        }
    },

    /**
     * Helpers <br>
     * These are functions intended to be called by other functions <br>
     * In affect these just help clean up other code
     */
    Helpers: {
        /**
         * Go To Path
         * @param path  {string}        Path to go to
         * @param data  {bool|Object}   Data to pass (for Get, ?key=value)
         * Acts like a href, for onclick
         */
        goToPath: function (path, data = false) {
            location.href = location.origin + (path ?? '/dashboard') + (data !== false ? PSA.Helpers.convertToGetData(data) : '');
        },

        /**
         *  Ajax Helper Function <br>
         *  Runs ajax call based on parameters and handles the errors
         * @param func              {string}            The function for the URL of the ajax call
         * @param data              {object}            The data to pass to the ajax call
         * @param successAction     {function}          Action to be preformed on Success, Passes response["data"] if available
         * @param desc              {string}            String description of what is happening (for swal messages, Ex: `Template Creation`)
         * @param isPOST            {boolean}           True= POST | False= GET | Default= True
         * @param successAlert      {boolean}           Whether to show alert for a success
         * @param form              {boolean}           Whether this is for a form
         * @param validationAction  {boolean|function}  False for nothing, or function to be preformed when validation fails
         */
        ajax: function (func, data, successAction, desc, isPOST = true, successAlert = false, form = false, validationAction = false) {
            // Step 1: Initialize Data
            // Setup Url
            let url = `/Data/${func}`;
            if(!isPOST){
                url += convertToGetData(data);
            }

            // Step 2: Ajax Call    TODO: test processData change
            $.ajax({ url: url, data: data, type: (isPOST ? 'POST' : 'GET'), async: true,
                // Success action
                success: function (response) {
                    // Convert response to object
                    let resp = JSON.parse(response);

                    // If internal success
                    if(resp['Success']){
                        // Add debug data
                        PSA.Debug.add(`${desc} Succeeded`, "Ajax", resp);

                        // Alert the user (if enabled)
                        if(successAlert) {  PSA.Helpers.Swal.success(`${desc} succeeded`); }

                        // Run success action, passing in data if possible
                        if(resp.hasOwnProperty('data')){
                            successAction(resp['data']);
                        } else {
                            successAction();
                        }
                        // If Invalid Form Error
                    } else if(form && !resp['valid']){
                        // Add debug data
                        PSA.Debug.add(`${desc} failed Validation`, "Ajax", resp);

                        // Run Validation action
                        if(validationAction !== false) {
                            validationAction(resp['errorsData']);
                        }
                        // Notify User
                        PSA.Helpers.Swal.validation(`${desc}`);
                        // Other Internal Error
                    } else {
                        // Add debug data
                        PSA.Debug.add(`${desc} failed`, "Ajax", resp);

                        // Notify User
                        PSA.Helpers.Swal.warn("Error", `${desc} failed`);
                    }
                },
                error: function (response) {
                    PSA.Debug.add(`${desc} failed`, "Ajax", response);

                    PSA.Helpers.Swal.error(`${desc} failed at Server`);
                }
            });
        },

        /**
         * Convert to Get Data
         * @param data {Object} Data to convert
         * @return string   Data to ammend to url
         */
        convertToGetData(data) {
            let urlData = "?";
            // Cycle through all key value pairs of data and converting them into a GET query
            for(const [key, value] of Object.entries(data)){
                urlData += `${key}=${value}&`;
            }
            // Remove Last '&'
            urlData = urlData.slice(0, -1);
            return urlData;
        },

        /**
         * SWAL <br>
         *     These all create SWAL messages with similar global styles
         */
        Swal: {
            // Simple SWAL
            /**
             * Show Validation Errors Message
             * @param entity    {String}    Your {entity} Form has Validation errors
             */
            validation: function(entity = '') {
                let msg = document.createElement('div');
                msg.innerHTML = `<span class='text-center'>Your ${entity} Form has validation errors, please review all of the <span class='text-danger'>red text</span> ` +
                    `to resolve errors.</span>`;
                let temp = { icon: "warning", title: "Validation Error"};
                PSA.Helpers.Swal.compile(temp, false, msg);
            },
            /**
             * Success Swal <br>
             * Title: Success <br>
             * Icon: Info
             * @param text  {String}    Text for Success Swal
             */
            success: function (text) {
                let temp = {icon: "info", title: "Success"};
                PSA.Helpers.Swal.compile(temp, text);
            },
            /**
             * Info Icon Swal
             * @param title     {String}            Title for Swal
             * @param text      {String|Boolean}    Text for Swal or empty for no-text
             * @param content   {Object|Boolean}    Alternative Data for Swal Body
             */
            info: function (title, text = false, content = false) {
                let temp = {icon: "info", title: title};
                PSA.Helpers.Swal.compile(temp, text, content);
            },
            /**
             * Warning Icon Swal
             * @param title     {String}            Title for Swal
             * @param text      {String|Boolean}    Text for Swal or empty for no-text
             * @param content   {Object|Boolean}    Alternative Data for Swal Body
             */
            warn: function (title, text = false, content = false) {
                let temp = {icon: "warning", title: title};
                PSA.Helpers.Swal.compile(temp, text, content);
            },
            /**
             * Error Icon Swal <br>
             * Title: Error
             * @param text      {String|Boolean}    Text for Swal or empty for no-text
             * @param content   {Object|Boolean}    Alternative Data for Swal Body
             */
            error: function (text = false, content = false) {
                let temp = {icon: "error", title: "Error", dangerMode: true};
                PSA.Helpers.Swal.compile(temp, text, content);
            },
            /**
             * Compiler For Swal <br>
             * Ensures all Swal that use this share styles
             * @param data      {Object}            Title and Icon Data
             * @param text      {String|Boolean}    Text for Swal or empty for no-text
             * @param content   {Object|Boolean}    Alternative Data for Swal Body
             */
            compile: function (data, text = false, content = false) {
                data.customClass = 'swal-dark';
                if (content !== false) data.html = content;
                if (text !== false) data.text = text;

                Swal.fire(data);
            },

            // Unique SWAL
            /**
             * Tutorial Swal <br>
             * Opens an extra-wide swal for holding custom content
             * @param title     {String}    Title for Swal
             * @param content   {Object}    HTML Element for Swal Body
             */
            tutorial: function (title, content) {
                Swal.fire({title: title, content: content, className: 'swal-dark-wide',});
            },

            // Complex SWAL
            /**
             * Confirmation <br>
             * These display a Swal Message with 2 buttons, if confirmed then an action with occur
             */
            Confirmation: {
                /**
                 * Delete Confirmation Swal <br>
                 * Icon: Warning <br>
                 * Title: Are you Sure?
                 * @param name          {String}    You will not be able to recover {name}
                 * @param successAction {Function}  Action to take if confirmed
                 * @param failAction    {Function|Boolean}  Action to take if not confirmed or empty for nothing
                 */
                delete: function (name, successAction, failAction = false) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Once deleted, you will not be able to recover ${name}!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, permanently delete them!',
                        className: 'swal-dark-confirm-delete',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            successAction();
                        } else if(failAction !== false) {
                            failAction();
                        }
                    });
                },

                /**
                 * Removal Confirmation Swal <br>
                 * Icon: Warning <br>
                 * Title: Are you Sure? <br>
                 * Like Delete except it says remove
                 * @param name          {String}    You will not be able to recover {name}
                 * @param successAction {Function}  Action to take if confirmed
                 * @param failAction    {Function|Boolean}  Action to take if not confirmed or empty for nothing
                 */
                remove: function (name, successAction, failAction = false) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Once removed, you will not be able to recover the data from ${name}!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, remove them!',
                        className: 'swal-dark-confirm-delete',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            successAction();
                        } else if(failAction !== false) {
                            failAction();
                        }
                    });
                },

                /**
                 * Special Confirmation Swal <br>
                 * Icon: Info <br>
                 * @param title         {String}    Title for Swal
                 * @param text          {String}    Text for Swal
                 * @param content       {Object}    HTML Element for Swal
                 * @param successAction {Function}  Action to take if confirmed
                 * @param failAction    {Function|Boolean}  Action to take if not confirmed or empty for nothing
                 */
                special: function (title, text, content, successAction, failAction = false) {
                    // TODO: Test and fix custom content
                    Swal.fire({ icon: "info", buttons: true, dangerMode: true, className: 'swal-dark-confirm-delete',
                        title: title, text: text, html: content,
                    }).then((willDelete) => {
                        if (willDelete) {
                            successAction();
                        } else {
                            if(failAction !== false) {
                                failAction();
                            }
                        }
                    });
                },
            },
            /**
             * Reload <br>
             * These do what the normal Swals do except afterwards they reload the webpage
             */
            Reload: {
                success: function (text) {
                    let temp = {icon: "info", title: "Success", text: text, className: 'swal-dark'};
                    Swal.fire(temp).then(function () {
                        window.location.reload();
                    });
                },
            },
            /**
             * Redirect <br>
             * These do what the normal Swal does except afterwards they redirect the webpage
             */
            Redirect: {
                success: function (text, href) {
                    let temp = {icon: "info", title: "Success", text: text, className: 'swal-dark'};
                    Swal.fire(temp).then(function () {
                        window.location.href = href;
                    });
                },
            },
        }
    },

    /**
     * Debug <br>
     * Logs and Tracks information for use debugging issues
     */
    Debug: {
        log: [],

        /** Add Debug <br>
         *  Adds the input data to Debug Log as a singular object
         * @param msg       {string}    Core message, what console.log would write out
         * @param source    {string}    Function that threw the message
         * @param data      {object}    Any extra abstract data
         */
        add: function (msg, source, data = {}) {
            PSA.Debug.log.push({message: msg, source: source, data: data})
        },

        getLatest: function (){
            return PSA.Debug.log[(PSA.Debug.log.length - 1)];
        },
    }
}
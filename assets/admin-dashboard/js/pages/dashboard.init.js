function checkCustomCompany(val){
    document.getElementById('companyCustom').style.display = (parseInt(val) <= 0) ? 'block' : 'none';
}

/**
 * createTemplate
 * @param company   Company for the new template
 */
function createTemplate(company){
    stashDebug(`Creating Template: ${company}`);
    // Initiate Creation
    $.ajax({ url: `/Data/AddTemplate`, data: {company: company}, type: 'POST', async: true,
        success: function(response) {
            stashDebug('Creating Template: Template created successfully: ' + response);
            let result = JSON.parse(response);
            $(`#templates_body`).append(`<tr id="template_${result.Message.tid}"><td>${result.Message.company_name}</td><td>${result.Message.name}</td><td>${result.Message.subject}</td><td>${result.Message.headers}</td><td><a href="/dashboard/template/${result.Message.tid}">Open Preview</a></td><td><a href="/dashboard/modifyTemplate/${result.Message.tid}"><i class="fa fa-pencil"></i></a><a href="#" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" onclick="javascript:confirmTemplateDelete(${result.Message.tid}, '${result.Message.name}', true);"><i class="fa fa-trash"></i></a></td></tr>`);
        },
        error: function (response) {
            stashDebug('Creating Template: Template creation failed: ' + JSON.stringify(response));
            swal({title: 'Error', text: 'Template creation failed!', icon: 'error'});
        }
    });
}

/**
 * confirmTemplateDelete
 * @param id            Template to delete
 * @param name          Name to use for confirmation message
 * @param unconfirmed   Whether the deletion is unconfirmed
 */
function confirmTemplateDelete(id, name = '', unconfirmed = true){
    if(unconfirmed){
        // Initiate Confirmation
        document.getElementById('confirmDeleteData').innerText = name;
        document.getElementById('confirmDeleteBtn').onclick = () => confirmTemplateDelete(id, name, false);
    } else {
        stashDebug("Deleted Template: " + id);
        // Initiate Deletion
        $.ajax({ url: '/Data/DeleteTemplate?id=' + id, type: 'GET', async: true,
            success: function(response) {
                stashDebug('Delete Template: Template deleted successfully: ' + response);
                $(`#template_${id}`).remove();
                $(`#confirmDeleteModal`).modal('toggle');
            },
            error: function (response) {
                stashDebug('Delete Template: Template deletion failed: ' + JSON.stringify(response));
                swal({title: 'Error', text: 'Template deletion failed!', icon: 'error'});
            }
        });
    }
}

let lastdebugMsg = [];
function stashDebug(msg){
    lastdebugMsg.push(msg);
    if(lastdebugMsg.length > 100) lastdebugMsg.pop();
}
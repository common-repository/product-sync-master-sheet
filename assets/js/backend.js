
jQuery(function($) {
    'use strict';
    $(document).ready(function() {
        //Has come from enqueue of frontend page loader
        $('p.pssg-error.pssg-limit-crossed').append(' <a href="#"> Get Unlimited</a>');
        $(document.body).on('click','p.pssg-error.pssg-limit-crossed, .errors-details-area.errors-details-area p.error-code',function(){
            var url = 'https://codeastrology.com/downloads/product-sync-master-sheet-premium/';
            window.open(url, '_blank'); // Opens the URL in a new tab
        });

        var ajax_url = PSSG_DATA.ajax_url;
        var ajaxurl = PSSG_DATA.ajax_url;
        var sync_btn_interval = PSSG_DATA.sync_btn_interval;
        var mainFormSelect = 'form#pssg-main-configuration-form';
        $(document.body).on('click','#pssg-syncronize-button',function(e){
            e.preventDefault();
            clearSheet();
            let mainWrapper = $(this).closest('#setup-wizard-section-wrapper');
            

            let btnIcon = $(this).find('span>i');
            let btnTextCont = $(this).find('strong.sync-btn-txt');
            let btnPrevText = btnTextCont.text();
            let btnCompleteText = PSSG_DATA.text.sync_done_msg;//Sync Done
            btnTextCont.html(PSSG_DATA.text.pause); //Pause
            btnIcon.addClass('animate-spin');
            let messageBox = $('.message-showing-area');
            messageBox.html(PSSG_DATA.text.syncing_msg); 

            let sync_status = mainWrapper.attr('data-sync_status');
            if( sync_status == 'playing' ){
                btnTextCont.html('Run'); 
                mainWrapper.attr('data-sync_status', 'paused');
                btnIcon.removeClass('animate-spin');
                messageBox.html(PSSG_DATA.text.paused); //Paused
                return;
            }else if( sync_status == 'paused' ){
                btnTextCont.html(PSSG_DATA.text.pause); //Pause
                mainWrapper.attr('data-sync_status', 'playing');
                return;
            }

            var errorsDetailsArea = $('.errors-details-area');

            mainWrapper.attr('data-sync_status', 'playing'); //Syncronizing...
            let countBox = $(this).find('.product_count');
            let paged = 1;
            let limit = $(this).data('limit');
            let nonce = $(this).data('nonce');
            let per_page = $(this).data('per_page');
            var syncronizeInterVal = setInterval(function(){

                let currentStatus = mainWrapper.attr('data-sync_status');
                console.log(currentStatus);
                if(currentStatus !== 'playing'){
                    messageBox.html(PSSG_DATA.text.paused); //Paused
                    return;
                }
                
                // messageBox.prepend('...');
                $.ajax({
                    type: 'POST',
                    url: ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'pssg_syncronize_products',
                        paged: paged,
                        nonce: nonce
                    },
                    beforeSend: function(){
        
                    },
                    complete: function(status){
                        $(document.body).trigger('pssg_syncronize_complete',status);
                    },
                    success: function (response) {
                        
                        paged++;
                        let count = response.count;
                        countBox.text(count);
                        console.log(response);
                        if(response.status == 'failed' && response.error == 'ProductEmpty'){
                            messageBox.html(PSSG_DATA.text.all_syncronized_msg);    //All Syncronized
                            btnTextCont.html(btnCompleteText);
                            clearInterval(syncronizeInterVal);
                            mainWrapper.attr('data-sync_status', 'waiting');
                            btnIcon.removeClass('animate-spin');
                            return;
                        }else if(response.status == 'failed'){
                            if(response.error == 'LimitCross'){
                                messageBox.html(PSSG_DATA.text.sync_limit_crossed); //Done and Limit Crossed!
                            }else{
                                messageBox.html(PSSG_DATA.text.check_error_msg); //Check following Error

                            }

                            //Errors handle Area
                            
                            errorsDetailsArea.find('.error-code').html(PSSG_DATA.text.response_error_code + response.error); //Error Code: 
                            $('.errors-details-area.errors-details-area p.error-code').append(' <a href="#"> Make Unlimited</a>');
                            var errorsListHtml = '';
                            $(response.errors).each(function(index,thisError){
                                errorsListHtml += "<p class='each-errors-item each-errors-" + index + "'>" + thisError + "</p>";
                            });
                            errorsDetailsArea.find('.errors-lists').html(errorsListHtml);

                            // messageBox.html("");
                            btnTextCont.html(btnPrevText);
                            clearInterval(syncronizeInterVal);
                            mainWrapper.attr('data-sync_status', 'waiting');
                            btnIcon.removeClass('animate-spin');
                            return;
                        }

                        //Only when from Server Response
                        if(response.status == 'success' && response.hasOwnProperty('data_response')){
                            let data_response = response.data_response;

                            if (data_response.hasOwnProperty('error')) {
                                let error = data_response.error;
                                let fullError = 'code: ' + error.code + ' | msg:' + error.message + ' | msg:' + error.status;
                                //Api or gConnection Error
                                fullError += "\n" + PSSG_DATA.text.check_connection_msg; //Please check connection again!
                                messageBox.html('Connection Error');
                                errorsDetailsArea.find('.error-code').html('GoogleSheet Error Code: ' + error.code);
                                let error_list = '<p class="error-code">- ' + error.message + '</p><p class="error-code">- ' + error.status + '</p>';
                                error_list += '<p class="error-code">Details check from console.</p>';
                                errorsDetailsArea.find('.errors-lists').html(error_list);
                                alert(fullError);
                                btnTextCont.html(btnPrevText);
                                clearInterval(syncronizeInterVal);
                                mainWrapper.attr('data-sync_status', 'waiting');
                                btnIcon.removeClass('animate-spin');
                                return;
                            }else if(data_response.hasOwnProperty('updatedRows')){
                                
                                let succMsg = PSSG_DATA.text.syncing_msg;
                                messageBox.html(succMsg);

                                if( ( count + per_page ) > limit){
                                    messageBox.html( PSSG_DATA.text.sync_done_msg );
                                    btnIcon.removeClass('animate-spin');
                                    clearInterval(syncronizeInterVal);
                                    mainWrapper.attr('data-sync_status', 'waiting');
                                    btnTextCont.html(btnCompleteText);
                                }
                            }
                        }
                        $(document.body).trigger('pssg_syncronize_response',response);
                    },
                    error:function(error){

                        btnTextCont.html(btnPrevText);       
                        messageBox.html('ErrorOnSyncronize');
                        btnIcon.removeClass('animate-spin');
                        clearInterval(syncronizeInterVal);
                        mainWrapper.attr('data-sync_status', 'waiting');
                        btnIcon.removeClass('animate-spin');
                        console.log(error);
                        $(document.body).trigger('pssg_syncronize_error',error);
                    }
                });
            }, sync_btn_interval); //sync_btn_interval = 5000 (mili seconds)
            
        });

        $(document.body).on('click','#pssg-send-apps-script',function(e){
            e.preventDefault();

            let btnIcon = $(this).find('span>i');
            let btnPrevIconClass = btnIcon.attr('class');
            let btnTextCont = $(this).find('strong.script-send-btn-txt');
            let btnPrevText = btnTextCont.text();
            let btnCompleteText = "Script Updated";
            btnTextCont.html("Sending Script..");
            btnIcon.addClass('pssg_icon-spin5');
            btnIcon.addClass('animate-spin');
            let messageBox = $('.send-script-message-area');
            messageBox.html('Sending Script...');

            $.ajax({
                type: 'POST',
                url: ajax_url,
                dataType: 'json',
                data: {
                    action: 'pssg_send_script',
                },
                beforeSend: function(){
    
                },
                complete: function(){
                    btnTextCont.html(btnCompleteText);
                    btnIcon.removeClass('animate-spin');
                    btnIcon.removeClass('pssg_icon-spin5');
                    btnIcon.addClass(btnPrevIconClass);
                },
                success: function (response) {
                    
                    console.log(response);
                    

                    //Only when from Server Response
                    if(response.status == 'success' && response.hasOwnProperty('data_response')){
                        let data_response = response.data_response;

                        if (data_response.hasOwnProperty('error')) {
                            let error = data_response.error;
                            let fullError = 'code: ' + error.code + ' | msg: ' + error.message + ' | msg: ' + error.status;
                            //Api or gConnection Error
                            fullError += "\nPlease check connection again.";
                            messageBox.html(fullError);

                            alert(fullError);
                            console.log(fullError);
                            btnTextCont.html(btnPrevText);
                            btnIcon.removeClass('animate-spin');
                            return;
                        }else{
                            messageBox.html('Script Updated');
                            
                            btnTextCont.html(btnCompleteText);
                            btnIcon.removeClass('animate-spin');
                            btnIcon.removeClass('pssg_icon-spin5');
                            btnIcon.addClass(btnPrevIconClass);
                            alert("Script Updated");
                        }
                    }else if(response == 0 || response == '0'){
                        
                        messageBox.html('Script Updated');
                            
                        btnTextCont.html(btnCompleteText);
                        btnIcon.removeClass('animate-spin');
                        btnIcon.removeClass('pssg_icon-spin5');
                        btnIcon.addClass(btnPrevIconClass);
                        alert("Script Updated");
                    }
                    
                },
                error:function(errResponse){
                    console.log(errResponse);
                    btnTextCont.html(btnPrevText);       
                    messageBox.html('ErrorOnSendScript');
                    btnIcon.removeClass('pssg_icon-spin5');
                    btnIcon.removeClass('animate-spin');
                    btnIcon.addClass(btnPrevIconClass);
                }
            });

        });

        function syncSheet( nonce ){
            let paged = 1;
            var syncronizeInterVal = setInterval(function(){
                $.ajax({
                    type: 'POST',
                    url: ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'pssg_syncronize_products',
                        paged: paged,
                        nonce: nonce
                    },
                    beforeSend: function(){
        
                    },
                    complete: function(status){
                        $(document.body).trigger('pssg_syncsheet_complete',status);
                    },
                    success: function (response) {
                        paged++;
                        console.log("Syncronizing...");
                        if(response.status == 'failed'){
                            console.log('Sync Exited');
                            clearInterval(syncronizeInterVal);
                            return;
                        }
                        $(document.body).trigger('pssg_syncsheet_response',response);
                    },
                    error:function(error){
                        console.log(error);
                        clearInterval(syncronizeInterVal);
                        $(document.body).trigger('pssg_syncsheet_response',error);
                    }
                });
            }, sync_btn_interval); 
            
        }

        function clearSheet(){
            $.ajax({
                type: 'POST',
                url: ajax_url,
                dataType: 'json',
                data: {
                    action: 'pssg_cleared_sheet',
                },
                beforeSend: function(){
    
                },
                complete: function(){
                },
                success: function (response) {
                    console.log('Sheet Cleared');
                    console.log(response);
                    $(document.body).trigger('pssg_clear_sheet_response',response);
                    
                },
                error:function(error){
                    console.log(error);
                }
            });
        }

        $(document.body).on('pssg_config_submit_response',function(e,response){
            //Currently Disabled
            return;
            let clear_sheet = response.clear_sheet;
            let again_sync = response.again_sync;
            let nonce = response.data.nonce;

            if( clear_sheet ){
                clearSheet();
            }
            if( again_sync ){
                setTimeout(function(){
                    syncSheet(nonce);
                }, sync_btn_interval);
            }
            
        });

        $(document.body).on('click','#pssg-clear-sheet-button',function(e){
            e.preventDefault();

            var conf = confirm(PSSG_DATA.text.delete_sheet); //Are you sure!\nWant to Destroy Sheet data.\n OK or Cancel.
            if(!conf){
                return;
            }

            let btnIcon = $(this).find('span>i');
            let btnTextCont = $(this).find('strong.delete-btn-txt');
            let btnPrevText = btnTextCont.text();
            let btnCompleteText = PSSG_DATA.text.sheet_clear;                //Sheet Cleared
            btnTextCont.html(PSSG_DATA.text.deleting_msg);                   //Deleteing..
            btnIcon.addClass('animate-spin');
            let messageBox = $('.message-showing-area');
            messageBox.html(PSSG_DATA.text.deleting_msg);                    //Deleting...

            $.ajax({
                type: 'POST',
                url: ajax_url,
                dataType: 'json',
                data: {
                    action: 'pssg_cleared_sheet',
                },
                beforeSend: function(){
    
                },
                complete: function(){
                    // btnTextCont.html(btnCompleteText);
                    btnIcon.removeClass('animate-spin');
                },
                success: function (response) {
                    console.log(response);
                    if(response.status == 'failed'){
                        //Errors handle Area
                        var errorsDetailsArea = $('.errors-details-area');
                        errorsDetailsArea.find('.error-code').html(PSSG_DATA.text.esponse_error_code + response.error);        //Error Code:
                        var errorsListHtml = '';
                        $(response.errors).each(function(index,thisError){
                            errorsListHtml += "<p class='each-errors-item each-errors-" + index + "'>" + thisError + "</p>";
                        });
                        errorsDetailsArea.find('.errors-lists').html(errorsListHtml);

                        // messageBox.html("");
                        btnTextCont.html(btnPrevText);
                        btnIcon.removeClass('animate-spin');
                        return;
                    }

                    //Only when from Server Response
                    if(response.status == 'success' && response.hasOwnProperty('data_response')){
                        let data_response = response.data_response;

                        if (data_response.hasOwnProperty('error')) {
                            let error = data_response.error;
                            let fullError = 'code: ' + error.code + ' | msg: ' + error.message + ' | msg: ' + error.status;
                            //Api or gConnection Error
                            fullError += "\n" + PSSG_DATA.text.check_connection_msg; //Please check connection again
                            messageBox.html(fullError);

                            alert(fullError);
                            console.log(fullError);
                            btnTextCont.html(btnPrevText);
                            btnIcon.removeClass('animate-spin');
                            return;
                        }else{
                            messageBox.html(PSSG_DATA.text.sheet_clear_msg); //Sheet has Cleared
                            
                            btnTextCont.html(btnPrevText);
                            btnIcon.removeClass('animate-spin');
                            alert(PSSG_DATA.text.sheet_clear_msg); //Sheet has Cleared
                        }
                    }else if(response == 0 || response == '0'){
                        
                        messageBox.html(PSSG_DATA.text.sheet_clear_msg); //Sheet has Cleared
                            
                        btnTextCont.html(btnPrevText);
                        btnIcon.removeClass('animate-spin');
                        alert(PSSG_DATA.text.sheet_clear_msg); //
                    }
                    $(document.body).trigger('pssg_clear_sheet_response',response);
                },
                error:function(error){

                    btnTextCont.html(btnPrevText);       
                    messageBox.html('ErrorOnDelete');
                    btnIcon.removeClass('animate-spin');
                    btnIcon.removeClass('animate-spin');
                    $(document.body).trigger('pssg_clear_sheet_error',error);
                }
            });

            
        });

        $(document.body).on('change','form#pssg-other-settings-form',function(event){
            var lastChangedElement = event.target;
            let lastParentDiv = $(event.target).closest('.pssg-field-wrapper-single');
            lastParentDiv.find('i.last-changed-element').remove();
            lastParentDiv.prepend('<i class="last-changed-element pssg_icon-spin5 animate-spin"></i>');

            $(this).submit();
        });
        $(document.body).on('change','form#pssg-main-configuration-form',function(event){
            var lastChangedElement = event.target;
            let lastParentDiv = $(event.target).closest('.pssg-field-wrapper-single');
            lastParentDiv.find('i.last-changed-element').remove();
            lastParentDiv.prepend('<i class="last-changed-element pssg_icon-spin5 animate-spin"></i>');
            var currentInput = lastParentDiv.find('input');
            currentInput.removeClass('pssg-error-empty-input');
            currentInput.closest('.pssg-field-wrapper-single').removeClass('pssg-empty-input-wrapper');
            currentInput.closest('label.switch').removeClass('pssg-empty-checkbox');
            $(this).submit();
        });
        $(document.body).on('submit', 'form#pssg-main-configuration-form', function (e){
            e.preventDefault();
            let submitBtn = $(this).find('button.configure_submit');
            let submitBtnTxtObj = submitBtn.find('strong.form-submit-text');
            let submitBtnPrevTxt = submitBtnTxtObj.text();
            let submitBtnInForm = submitBtn.not('.float-btn');
            let submitBtnIcon = submitBtn.find('span i');
            let submitBtnPrevIcon = submitBtnIcon.attr('class');
            submitBtnTxtObj.text(PSSG_DATA.text.saving_msg);                                  //Saving...
            submitBtnIcon.attr('class', 'pssg_icon-spin5 animate-spin');
            // submitBtnIcon.attr('class', 'wpt-floppy');
            

            
            var formElement = $(this);
            // Parse serialized data and convert to an object
            var formData = generateFormData( formElement );
            
            
                console.log('formData');
                console.log(formData);
                $(document.body).trigger('pssg_setup_wizard_formdata',formData);
            // AJAX request
            $.ajax({
                type: 'POST',
                url: ajaxurl, // You need to define this in your PHP or localize it
                data: {
                    action: 'pssg_setting_wizard_submit', // This is the WordPress AJAX action hook
                    data: formData
                },
                complete:function(){
                    submitBtnTxtObj.text(submitBtnPrevTxt);
                    submitBtnIcon.attr('class', submitBtnPrevIcon);
                    submitBtnIcon.removeClass('animate-spin');

                    let lastChangeEl = $('i.last-changed-element');
                    if(lastChangeEl.hasClass('animate-spin')){
                        lastChangeEl.addClass('pssg_icon-ok-circle').removeClass('pssg_icon-spin5').removeClass('animate-spin');
                    }
                    setTimeout(function(){
                        lastChangeEl.fadeOut();
                    },1000);
                    
                },
                success: function(response) {

                    
                    // console.log(response);
                    
                    try{
                        submitDataHandle(response);
                        //Errors Handle and organized
                        submitErrorsHandle(response);

                    }catch(error){
                        console.log(error);

                    }
                    $(document.body).trigger('pssg_setup_wizard_response',response);
                    
                },
                error: function(error) {
                    // Handle errors
                    console.log(error.responseText);
                }
            });

            
        });

        //For Setting page or other setting configuration page
        $(document.body).on('submit', 'form#pssg-other-settings-form', function (e){
            e.preventDefault();
            let submitBtn = $(this).find('button.configure_submit');
            let submitBtnTxtObj = submitBtn.find('strong.form-submit-text');
            let submitBtnPrevTxt = submitBtnTxtObj.text();
            let submitBtnInForm = submitBtn.not('.float-btn');
            let submitBtnIcon = submitBtn.find('span i');
            let submitBtnPrevIcon = submitBtnIcon.attr('class');
            submitBtnTxtObj.text(PSSG_DATA.text.saving_msg);                                  //Saving...
            submitBtnIcon.attr('class', 'pssg_icon-spin5 animate-spin');

            var formElement = $(this);
            // Parse serialized data and convert to an object
            var formData = generateFormData( formElement );
                console.log(formData);
                $(document.body).trigger('pssg_config_submit_formdata',formData);
            // AJAX request
            $.ajax({
                type: 'POST',
                url: ajaxurl, // You need to define this in your PHP or localize it
                data: {
                    action: 'pssg_configure_form_submit', // This is the WordPress AJAX action hook
                    data: formData
                },
                complete:function(status){
                    submitBtnTxtObj.text(submitBtnPrevTxt);
                    submitBtnIcon.attr('class', submitBtnPrevIcon);
                    submitBtnIcon.removeClass('animate-spin');

                    let lastChangeEl = $('i.last-changed-element');
                    if(lastChangeEl.hasClass('animate-spin')){
                        lastChangeEl.addClass('pssg_icon-ok-circle').removeClass('pssg_icon-spin5').removeClass('animate-spin');
                    }
                    setTimeout(function(){
                        lastChangeEl.fadeOut();
                    },1000);
                    $(document.body).trigger('pssg_config_submit_complete',status);
                },
                success: function(response) {
                    $(document.body).trigger('pssg_config_submit_response',response);
                    console.log(response);
                    
                },
                error: function(error) {
                    $(document.body).trigger('pssg_config_submit_error',error);
                    // Handle errors
                    console.log(error.responseText);
                }
            });

            
        });

        function generateFormData( formElement ){
            // Serialize form data
            var serializedFormData = formElement.serialize();

            // Parse serialized data and convert to an object
            var formData = {};
            
            serializedFormData.split('&').forEach(function(pair) {
                pair = pair.split('=');
                var formKey = pair[0];
                var formVal = decodeURIComponent(pair[1] || '');
                
                formKey = decodeURIComponent(formKey) || '';

                // Handle array values
                if (/\[\]$/.test(formKey)) {
                    formKey = formKey.replace('[]', ''); // Remove [] from the key
                    formData[formKey] = formData[formKey] || [];
                    formData[formKey].push(formVal);
                } else if (/\[.*?\]/.test(formKey)) {
                    // Handle nested arrays
                    var keys = formKey.match(/([^\[\]]+)/g);
                    var lastKey = keys.pop();

                    var currentObj = formData;
                    keys.forEach(function(key) {
                        currentObj[key] = currentObj[key] || {};
                        currentObj = currentObj[key];
                    });

                    currentObj[lastKey] = formVal;
                } else {
                    formData[formKey] = formVal;
                }
            });

            return formData;
        }

        function submitDataHandle(response){
            let errors = response.errors;
            let data = response.data;
            let sheet_url = data.sheet_url;
            let checkout_sheet_url = $('.checkout-sheet-url');
            if(! errors.hasOwnProperty('sheet_url')){
                checkout_sheet_url.attr('href', sheet_url).show();
                checkout_sheet_url.html(PSSG_DATA.text.checkout_sheet);  //Checkout your Sheet
            }else{
                checkout_sheet_url.attr('href', 'https://docs.google.com/spreadsheets/#saiful');
                checkout_sheet_url.html(PSSG_DATA.text.create_sheet); //Create Sheet
            }
        }
        function submitErrorsHandle(response){
            let syncroWrapper = $('.syncronize-wrapper');

            let errorsWrapper = $('.submit-errors-wrapper');

            let errorsObject = response.errors;

            // Check if errorsObject is not null or undefined
            if (errorsObject) {
                // Count the number of errors
                let errorCount = Object.keys(errorsObject).length;

                if( errorCount > 0){
                    syncroWrapper.addClass('syncronize-submit-errors');
                }else{
                    syncroWrapper.removeClass('syncronize-submit-errors');
                }

                let errsHtml = '';
                // Iterate through each error
                for (let key in errorsObject) {
                    if (errorsObject.hasOwnProperty(key)) {
                        let error = errorsObject[key];
                        errsHtml += '<p class="subm-errs-' + key + '"><i class="pssg_icon-info-circled"></i>' + error + '</p>'
                    }
                }
                errorsWrapper.html(errsHtml);
            }

            
        }


        var uploadContainer = $('#json-upload-container');
        var uploaderNonce = uploadContainer.data('nonce');
        var hiddenInput = $('#json-upload-container-input');
        var fileInput = $('#json-file-input');
        var fileDelete = $('i.delete-old-file-data');
        var progressBar = $('#json-upload-progress-bar');
        var animateSpin = uploadContainer.find('.pssg-animate-wrapper');
        var messageContainer = $('#json-upload-message');
        var messageContainerSuccess = $('#json-upload-message-success');
        var messageContainerError = $('#json-upload-message-errors');

        uploadContainer.on('dragover', function (e) {
            e.preventDefault();
            uploadContainer.addClass('dragover');
        });

        uploadContainer.on('dragleave', function () {
            uploadContainer.removeClass('dragover');
        });

        uploadContainer.on('drop', function (e) {
            
            e.preventDefault();
            animateSpin.fadeIn();
            uploadContainer.removeClass('dragover');

            var files = e.originalEvent.dataTransfer.files;
            handleFiles(files);
        });

        fileInput.on('change', function () {
            animateSpin.fadeIn();
            var files = fileInput[0].files;
            handleFiles(files);
        });

        fileDelete.on('click', function(){
            var conf = confirm(PSSG_DATA.text.json_file_delating); //Are you sure!\nDelete Service JSON file.\n OK or Cancel.
            if(conf){
                animateSpin.fadeIn();
                var formData = new FormData();
                    formData.append('action', 'handle_json_file_upload');
                    formData.append('delete', 'json');
                    formData.append('nonce', uploaderNonce);


                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxurl, true);



                    xhr.onload = function () {
                        let response = xhr.response;
                        let status = xhr.status;
                        console.log(status);
                        
                        response = JSON.parse(response);

                        if(response.status == 'success'){
                            hiddenInput.val('');
                            var successMess = response.message;
                            uploadContainer.removeClass('success-on-json');
                            uploadContainer.removeClass('error-on-json');
                            uploadContainer.addClass('no-file-founded');
                            $('.pssg-client-email').val('');
                            messageContainerSuccess.html("<span>" + successMess + "</span>");
                            messageContainerError.html("");
                            messageContainer.html("");
                            progressBar.hide();
                            $(mainFormSelect).submit();
                        }else{
                            hiddenInput.val('');
                        }

                        console.log(response);
                        
                        animateSpin.fadeOut();
                    };
                    xhr.send(formData);

            }else{
                return;
            }
        });


        function handleFiles(files) {
            messageContainerSuccess.html("");
            messageContainerError.html("");
            messageContainer.html("");
            if (files.length > 0) {
                var file = files[0];
        
                if (file.type === 'application/json') {
                    uploadContainer.removeClass('error-on-json');
                    messageContainer.html( PSSG_DATA.text.file_selecting_msg + " " + file.name );    //File selected: 
        
                    var formData = new FormData();
                    formData.append('action', 'handle_json_file_upload');
                    formData.append('json_file', file);
                    formData.append('nonce', uploaderNonce);
        
                    // Use AJAX to send the file to the server
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxurl, true);


                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            var percent = (e.loaded / e.total) * 100;
                            progressBar.css('width', percent + '%');
                            progressBar.html(percent + '%');
                            console.log(e.loaded, e.total);
                        }
                    });

                    xhr.onload = function () {
                        let response = xhr.response;
                        let status = xhr.status;
                        
                        response = JSON.parse(response);
                        console.log("Upload File JSON");
                        console.log(response);
                        if(response.status == 'failed'){
                            uploadContainer.addClass('error-on-json');
                            uploadContainer.removeClass('success-on-json');
                            let errorMess = '';
                            $(response.errors).each(function(index,errorMessage){
                                errorMess += '<span>' + errorMessage + '</span>';
                                
                            });
                            messageContainerError.html(errorMess);
                            hiddenInput.val('');
                        }else if(response.status == 'success'){
                            uploadContainer.removeClass('error-on-json');
                            uploadContainer.removeClass('no-file-founded');
                            uploadContainer.addClass('success-on-json');
                            uploadContainer.find('.old-file-data span').html(file.name);
                            let successMess = '';
                            $(response.message).each(function(index,successMessage){
                                successMess += '<span>' + successMessage + '</span>';
                                
                            });
                            $('.pssg-client-email').val(response.client_email);
                            messageContainerSuccess.html(successMess);
                            messageContainerError.html("");
                            hiddenInput.val(file.name);
                        }
                        animateSpin.fadeOut();
                        $(mainFormSelect).submit();
                    };
                    xhr.send(formData);
                } else {
                    hiddenInput.val('');
                    uploadContainer.addClass('error-on-json');
                    uploadContainer.removeClass('success-on-json');
                    messageContainer.html('<span class="invalid-file-type">' + PSSG_DATA.text.invalid_file_type + '</span>'); //Invalid file type. Please select a JSON file.
                    animateSpin.fadeOut();
                }
            }
        }




    
        $(window).on('scroll',function(){
            let topbarElement = $('div.pssg-header.pssg-clearfix');
            
            let scrollTop = $(this).scrollTop();

            let configFormElement = $('form#pssg-main-configuration-form').not('.setup-wizard-form');
            if(configFormElement.length < 1) return;
    
            if(scrollTop > 50){
                configFormElement.addClass('topbar-fixed-on-scroll-main-element');
                topbarElement.addClass('topbar-fixed-on-scroll');
            }else{
                configFormElement.removeClass('topbar-fixed-on-scroll-main-element');
                topbarElement.removeClass('topbar-fixed-on-scroll');
            }


        });

        /**
         * Tab Area Handle
         */
        configureTabAreaAdded('#pssg-other-settings-form'); //Specially for Configure Page
        
        function configureTabAreaAdded( mainSelector = '#pssg-other-settings-form' ){
            var tabSerial = 0;
            var tabArray = new Array();
            var tabHtml = ""
            var tabArea = $(mainSelector + ' .pssg-configure-tab-wrapper');

            if(tabArea.length < 1){
                $(mainSelector).prepend('<div class="pssg-configure-tab-wrapper pssg-section-panel no-background"></div>');
                tabArea = $(mainSelector + ' .pssg-configure-tab-wrapper');
            }
            var sectionPanel = $(mainSelector + ' div.pssg-section-panel');
            sectionPanel.each(function(index, content){
                
                

                let table = $(this).find('table');
                let tableCount = table.length;
                let icon = $(this).data('icon');
                if(tableCount > 0){
                    
                    let firstTable = table.first();
                    let tableId = $(this).attr('id');

                    if(!tableId){
                        tableId = 'section-panel-' + index;
                        $(this).attr('id', tableId);
                    }
                    let tableTitle = firstTable.find('thead tr th:first-child h3').html();
                    var tempDiv = $('<div>').html(tableTitle);
                    tempDiv.find('a').remove();
                    tableTitle = tempDiv.text();
                    tabArray[tableId] = tableTitle;

                    let iconTag = '<i class="' + icon + '"></i>';
                    let menuTitle = iconTag + tableTitle;
                    if(tabSerial !== 0){
                        $(this).hide();
                        tabHtml += "<a href='#" + tableId + "' class='tab-button pssg-button link-" + tableId + "'>" + menuTitle + "</a>"
                    }else{
                        $(this).addClass('active');
                        tabHtml += "<a href='#" + tableId + "' class='tab-button pssg-button link-" + tableId + " active'>" + menuTitle + "</a>"
                    }

                    // let secId = $(this).attr('id');
                    // if(secId == 'pssg-syncronize-settings'){
                    //     $(this).show();
                    // }

                    tabSerial++;

                }
                
            });
            // if(tabSerial > 2){
            //     tabHtml += "<a href='#show-all' class='tab-button pssg-button'><i class='pssg_icon-globe-inv'></i>" + PSSG_DATA.text.show_all + "</a>"; //Show All
            // }
            tabArea.html(tabHtml);
            
        }

        let target = window.location.hash;
        let last_target = '';
        if(target !== ''){
            showTargetElement(target);
        }

        $(document.body).on('click','.pssg-configure-tab-wrapper a.tab-button',function(e){
            e.preventDefault();
            let target = $(this).attr('href');
            // window.location.hash = target;
            history.pushState(null, null, target);
            showTargetElement(target);
            
        });

        function showTargetElement(target,e){

            if(target == last_target){
                return;
            }
            last_target = target;

            let mainSelector = '#pssg-other-settings-form';
            var sectionPanel = $(mainSelector + ' div.pssg-section-panel');
            if(sectionPanel.length < 3){
                return;
            }
            $('.pssg-configure-tab-wrapper a').removeClass('active');
            $('.pssg-configure-tab-wrapper a[href="' + target + '"]').addClass('active');
            // $(this).addClass('active');
            $(mainSelector + ' div.pssg-section-panel.active').hide();
            if(target == '#show-all'){
                sectionPanel.not('#pssg-getting-start-settings').fadeIn();
                return;
            }
            
            let submitButton = $('#pssg-form-submit-button');
            if(target == '#pssg-getting-start-settings'){
                submitButton.hide();
                // $('#pssg-syncronize-settings').hide();
            }else{
                submitButton.fadeIn();
                // $('#pssg-syncronize-settings').show();
            }
            $(mainSelector + ' ' + target).fadeIn('fast').addClass('active');
            $(document.body).trigger('pssg_current_target_tab',target);
        }


        $(document.body).on('click','.pssg-client-email',function(){
            $(this).select();
            var client_email_copy_msg = PSSG_DATA.text.email_copied_msg; //Copied, Add this email as yoru Sheet\'s Editor
            try{
                document.execCommand('copy');
                window.getSelection().removeAllRanges();
                alert(client_email_copy_msg);
            }catch(error){
                alert(client_email_copy_msg);
            }
        });
        $(document.body).on('click','#pssg-appscript-copy-button',function(){
            var msgBox = $('.script-copy-msg');
            var contentToCopy = document.getElementById('appscript-code-area');
            // Check if the Clipboard API is supported
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(contentToCopy.textContent)
                    .then(function () {
                        // Success! You can provide user feedback here if needed
                        msgBox.text(PSSG_DATA.text.script_copied_msg).addClass('success'); //Script Copied! - Now add to your Sheet.
                    })
                    .catch(function (err) {
                        // Handle errors
                        msgBox.text(PSSG_DATA.text.error_in_copy).addClass('success');     //Unable to copy to clipboard
                        console.log( err);
                    });
                    setTimeout(function(){
                        msgBox.text("");
                    }, 1500);
            } else {

                $('head').append('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css" type="text/css" />');
        // Load CodeMirror JS
        $.getScript("https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js", function() {
          $.getScript("https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/javascript/javascript.min.js", function() {
            // Initialize CodeMirror after scripts are loaded
            var editor = CodeMirror.fromTextArea(document.getElementById('appscript-code-area'), {
              lineNumbers: true,
              mode: "javascript",
              theme: "default",
            //   lineWrapping: true
            });
          });
        });

                $(this).hide();
                $('.CodeMirror').css({
                    opacity: 1,
                    height: '55px !important',
                    display: 'block',
                    marginTop: '10px'
                });
                // Provide user feedback if needed
                msgBox.text('Please copy the AppsScript manually.').addClass('warning');         //Content Copied!
            }
        });


        //For Quick Edit Table
        $('.pssg-quick-edit-table td[contenteditable="true"]').on('blur', function() {
            
            var keyword,keywordLabel,tdValue,val,orignalVal;
            val = $(this).text();
            orignalVal = $(this).attr('original');

            if(val == orignalVal){
                return;
            }

            var thisRow = $(this).closest('tr.pssg-quick-table-tr');
            thisRow.addClass('editing-row');

            var params = {};
            thisRow.find('td.each-cell').each(function(){
                keyword = $(this).data('keyword');
                keywordLabel = $(this).data('keyword_label');
                tdValue = $(this).text();

                params[keywordLabel] = tdValue;
            });
           
            var nonce = thisRow.closest('.pssg-quick-edit-table').data('nonce');

            $.ajax({
                type: 'POST',
                url: ajax_url,
                data: {
                    action: 'pssg_quick_table_update',
                    params:params,
                    nonce: nonce
                },
                beforeSend: function(){
    
                },
                complete: function(status){
                    thisRow.removeClass('editing-row');
                    thisRow.addClass('product-edited');
                    $(document.body).trigger('pssg_quick_edit_on_blus_complete',status);
                },
                success: function (response) {
                    $(document.body).trigger('pssg_quick_edit_on_blus_response',response);
                    console.log(JSON.parse(response));
                },
                error:function(error){
                    thisRow.removeClass('editing-row');
                    thisRow.addClass('product-edited');
                    $(document.body).trigger('pssg_quick_edit_on_blus_complete',error);
                }
            });

            
        });
        $('.pssg-quick-edit-table td[contenteditable="true"]').on('keydown', function(e) {
            // Handle keypress event to prevent new lines on Enter
            if (e.which === 13) {
                e.preventDefault();
      
                // Set focus to the next row in the same column
                var columnIndex = $(this).index();
                var nextRow = $(this).closest('tr').next();
                var nextCell = nextRow.find('td').eq(columnIndex);
                
                var nextCellEditAble = nextCell.attr('contenteditable');
                if(nextCellEditAble == 'false'){
                    $(this).next().focus();
                    return;
                }
                
                // Check if there is a next row and cell
                if (nextRow.length && nextCell.length) {
                  nextCell.focus();
                }else{
                    $(this).next().focus();
                }
              }
        });
        $('.pssg-quick-edit-table td[contenteditable="true"]').on('focus', function() {
            var val = $(this).text();
            $(this).attr('original', val);
        });

        //For Reset added on new branch 1.0.1.1 for reset setting
        $(document.body).on('click','.pssg-reset-all-setting-wizard',function(e){
            e.preventDefault();
            if (confirm('Are you sure you want to reset and reload the page?')) {
                $('.pssg-hidden-reset-input').attr('name','reset');
                $('.pssg-hidden-reset-input').val('reset');

                var Wrapper = $(this).closest('.setup-wizard-section-wrapper');
                var stepWrapper = Wrapper.find('.pssg_progress_container');
                stepWrapper.find('.pssg_progress').css('width', '0%');
                var progressBar = stepWrapper.find('.pssg_circle');
                progressBar.removeClass('active');
                progressBar.removeClass('already-activated');
                progressBar.first().addClass('active');
                progressBar.first().addClass('already-activated');


                var Panel = Wrapper.find('div.pssg-setup-wizard-panel');

                Panel.removeClass('active');
                Panel.first().addClass('active');
                Panel.find('input').val('');
                
                $(document.body).trigger('pssg_setup_wizard_reset',e);
                
                $('.pssg-stats-wrapper span.total-percent-n').html('0%');
                Wrapper.find('.pssg-current-page-input').val(1);
                Wrapper.find('.pssg-last-page-input').val(1);
                $(mainFormSelect).submit();
            }
        });
        $(document.body).on('change','#pssg-show-tutorial-checkbox',function(){
            var Wrapper = $(this).closest('.setup-wizard-section-wrapper');
            Wrapper.toggleClass('pssg-tutorial-show');
            if($(this).is(':checked')){
                Wrapper.find('.step-wizard-details-tuts').show();
            }else{
                Wrapper.find('.step-wizard-details-tuts').hide();
            }
        });

        $(document.body).on('click','a.pssg-show-tips',function(e){
            e.preventDefault();
            $(this).closest('div.pssg-setup-wizard-panel').find('.step-wizard-details-tuts').fadeToggle();
        });


        //For setting wizard - Main Page actually now
        $(document.body).on('click','.pssg-setup-wizard-next-button',function(e){
            
            e.preventDefault();
            var Wrapper = $(this).closest('.setup-wizard-section-wrapper');
            var thisPanel = $(this).closest('div.pssg-setup-wizard-panel');
            var thisPanelIndex = thisPanel.data('index');
            var thisPageKey = thisPanel.data('page_key');
            
            var currentInput = thisPanel.find("input[name=" + thisPageKey + "]");
            var type = currentInput.attr('type');
            if(typeof currentInput == 'object' && currentInput.length > 0){
                var currentInputVal = currentInput.val();
            }else if(type == 'checkbox'){
                var currentInputVal = '';
            }
            var checked = currentInput.is(':checked');


            if( (currentInputVal == '' && type !== 'checkbox') || ( type == 'checkbox' && !checked ) ){
                currentInput.addClass('pssg-error-empty-input');
                currentInput.closest('.pssg-field-wrapper-single').addClass('pssg-empty-input-wrapper');
                currentInput.closest('label.switch').addClass('pssg-empty-checkbox');
                return;
            }else{
                currentInput.removeClass('pssg-error-empty-input');
                currentInput.closest('.pssg-field-wrapper-single').removeClass('pssg-empty-input-wrapper');
                currentInput.closest('label.switch').removeClass('pssg-empty-checkbox');
            }
            
            thisPanelIndex = parseInt(thisPanelIndex);
            var nextPanelIndex = thisPanelIndex + 1;
            thisPanelIndex = parseInt(thisPanelIndex);
            
            var nextPanel = thisPanel.next();
            var nextPanelIndex = nextPanel.data('index');
            nextPanelIndex = parseInt(nextPanelIndex);
            if(!nextPanelIndex){
                alert("There is no next panel");
                return;
            }
            thisPanel.removeClass('active');
            nextPanel.addClass('active');

            let lastPage = Wrapper.find('.pssg-last-page-input').val();
            lastPage = parseInt(lastPage);
            if(lastPage < nextPanelIndex){
                lastPage = nextPanelIndex;       
            }
            let count = $('.pssg_progress_container .pssg_circle').length;
            count = parseInt(count);
            let completedPercentage = ( (100 / count ) * lastPage );
            Wrapper.find('.pssg-current-page-input').val(nextPanelIndex);
            Wrapper.find('.pssg-last-page-input').val(lastPage);

            
            updateStepBar(Wrapper, nextPanelIndex, true);

            // $(mainFormSelect).submit();
            let next_args = {
                thisPanelIndex: thisPanelIndex,
                thisPageKey: thisPageKey,
                page_key: thisPageKey,
                lastPage: lastPage,
                count: count,
                completedPercentage: completedPercentage
            };
            $('.pssg-stats-wrapper .total-percent-n').html(completedPercentage + '%');
            $(document.body).trigger('pssg_setup_wizard_next_button',next_args);
        });

        //For setting wizard - Main Page actually now
        $(document.body).on('click','.pssg_circle',function(e){
            e.preventDefault();
            var WrapperCircle = $(this);
            updateStepUpto(WrapperCircle);
            
        });

        //For setting wizard - Main Page actually now
        function updateStepUpto(WrapperCircle){
            if(! WrapperCircle.hasClass('already-activated')){
                return;
            }
            var Wrapper = WrapperCircle.closest('.setup-wizard-section-wrapper');
            
            var thisPanelIndex = WrapperCircle.data('index');
            thisPanelIndex = parseInt(thisPanelIndex);
            var thisPanelkey = WrapperCircle.data('page_key');
            
            if(thisPanelkey == 'finish'){
                $('.pssg_progress_container').css('display','none');
                $('.modify-setup-wizard-wrapper').show();
            }
            //Show current and active panel section / details bottom part actually
            var thisPanel = Wrapper.find('div#pssg-' + thisPanelkey + '-setup-wizard');
            var allPanel = Wrapper.find('.pssg-setup-wizard-panel');
            allPanel.removeClass('active');
            thisPanel.addClass('active');

            Wrapper.find('.pssg-current-page-input').val(thisPanelIndex); //To update last page or current page
            
            updateStepBar(Wrapper, thisPanelIndex);

            // $(mainFormSelect).submit();
        }

        //For setting wizard - Main Page actually now
        function updateStepBar(Wrapper, thisPanelIndex, singlePage = false){
            var progressBar = Wrapper.find('.pssg_progress_container');
            let count = progressBar.find('.pssg_circle').length;
            count = parseInt(count);
            let current_page = thisPanelIndex;
            let page_key = progressBar.find('.pssg_circle[data-index=' + current_page + ']').data('page_key');
            Wrapper.attr('data-current_page_key', page_key);
            Wrapper.attr('data-current_page', current_page);
            let widthPercentage = ( (100 / ( count -1 ) ) * ( current_page - 1 ) );
            let progressPercent = ( (100 / ( count ) ) * ( current_page) );
            progressBar.find('.pssg_progress').css('width', widthPercentage + '%');
            
            for(var i=1; i <= 10; i++){
                if(i <= current_page){
                    progressBar.find('.pssg_circle[data-index=' + i + ']').addClass('active');
                    progressBar.find('.pssg_circle[data-index=' + i + ']').removeClass('not-active');
                    progressBar.find('.pssg_circle[data-index=' + i + ']').removeClass('not-activated-yet');
                }else{
                    progressBar.find('.pssg_circle[data-index=' + i + ']').removeClass('active');
                }
            }
            if(singlePage){
                let last_page_key = progressBar.find('.pssg_circle[data-index=' + current_page + ']').data('page_key');
                Wrapper.attr('data-last_page_key', last_page_key);
                progressBar.find('.pssg_circle[data-index=' + current_page + ']').addClass('already-activated');
                progressBar.find('.pssg_circle[data-index=' + current_page + ']').removeClass('not-activated-yet');
                progressBar.find('.pssg_circle[data-index=' + current_page + ']').removeClass('not-active');
            }
            progressBar.find('.pssg_circle').removeClass('last-activated');
            progressBar.find('.pssg_circle[data-index=' + current_page + ']').addClass('last-activated');
            let target_args = {
                current_page: current_page,
                page_key: page_key,
                thisPanelkey: page_key,
                widthPercentage: widthPercentage,
                progressPercent: progressPercent,
                count: count,
                singlePage: singlePage,
                byNextBtnClick: singlePage,
                
            };
            console.log(target_args);
            $(mainFormSelect).submit();
            
            $(document.body).trigger('pssg_setup_wizard_target', target_args);
        }

        //Findining Error Count
        $(document.body).on('pssg_setup_wizard_response',function(e, response){
            
            var errors = response.errors;
            var data = response.data;
            let reset = data['reset'];
            if(reset){
                $('.pssg-hidden-reset-input').val('');
            }
            update_sheet_link(data);

            console.log(data);
            console.log(errors);
            update_step_box_class(errors);

        });

        $(document.body).on('pssg_setup_wizard_next_button',function( e, args ){
            console.log('args');
            console.log(args);
        });

        //For setting wizard - Main Page actually now
        $(document.body).on('click','.modify-setup-wizard-wrapper .pssg_modify_setup_wizard',function(e){
            e.preventDefault();
            // $('.pssg_progress_container').show();
            $('.pssg_progress_container').css('display','flex');
            $('.modify-setup-wizard-wrapper').hide();
        });

        var update_sheet_link = function( data_respnse ){
            let sheet_url = data_respnse['sheet_url'];
            let form_sheet_link_el = $('.pssg-form-info.sheet-url-info a.pssg-doc-link');
            if(sheet_url){
                form_sheet_link_el.attr('href',sheet_url).html('Your Google Sheet');
            }else{
                form_sheet_link_el.attr('href','https://docs.google.com/spreadsheets/#saiful').html('Google Sheet');
            }
        }

        var update_step_box_class = function ( errors ){
            var errorCount = errors ? Object.keys(errors).length : 0;
            $('.pssg_progress_container .pssg_circle').removeClass('error-founded');
            if(errorCount > 0){
                $.each(errors, function(key, value) {
                    $('.pssg_progress_container .pssg_circle[data-page_key=' + key + ']').addClass('error-founded');
                });
            }
        }










        // Premium Content Start Here
        $(document.body).on('click','tr.pssg-premium-row span.pssg-config-close-btn',function(){
            let count = $('.pssg-cf-wrapper.pssg-cf-wrapper-single').length;
            if(count == 1){return;}
            $(this).closest('.pssg-cf-wrapper').remove();
            $('.added-cf-single').last().find('input').change();
        });

        /**
         * For setting - Custom Field cf actually now
         */
        $(document.body).on('pssg_config_submit_formdata',function(e,formData){
            e.preventDefault();
            if($('.pssg-cf-wrapper.pssg-cf-wrapper-single').length < 1){
                return;
            }
            var added_cf = formData.added_cf;
            if(!added_cf){
                return;
            }
            let lastValue = Object.values(added_cf).pop();
            let keyword = lastValue.keyword;
            let title = lastValue.title;
            console.log(keyword,title);
            if( keyword == '' || title == '' ){
                return;
            }

            let count = $('tr.pssg-premium-row .pssg-cf-wrapper.pssg-cf-wrapper-single').length;
            count += Math.floor(Math.random() * 200) + 1;
let newCustomField =  `<div class="pssg-cf-wrapper pssg-cf-wrapper-single">
<div class="added-cf-single">
    <input placeholder="Custom Field Keyword" type="text" name="added_cf[`+count+`][keyword]" value="" id="cf-keyword-`+count+`">
</div>
<div class="added-cf-single">
    <input  placeholder="Title for Row Column" type="text" name="added_cf[`+count+`][title]" value="" id="cf-title-`+count+`">
</div>
<span class="pssg-config-close-btn"><i class="dashicons dashicons-no-alt"></i></span>    
</div> `;            

let newCheckBox = `<p class="each-checkbox">
<input type="checkbox" id="checkbox-for-col-min_quantity" name="hide_columns[`+keyword+`]" value="on">
<label for="checkbox-for-col-min_quantity">`+title+`</label><br>
</p>`;
$('.pssg-checkbox-group.pssg-checkbox-group-columns-setting').append(newCheckBox);


            $('tr.pssg-premium-row .pssg-cf-wrapper.pssg-cf-wrapper-single').last().after(newCustomField);    
        });
        // Premium Content End Here
    });
});
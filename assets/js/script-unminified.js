var $website_routespace = "pssg_namespace_route";
var $website_routespace_multiple = "multiple_product_route";
var $website_wpjson = "pssg_json_website";


function onOpen(e){
    var spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    // Get the active spreadsheet
    var sheet = spreadsheet.getActiveSheet();
    var thisTabName = sheet.getName();
    if( YourSheetTabName !== '' && YourSheetTabName !== thisTabName ){
        return;
    }
   
   var lastCell = sheet.getLastColumn();
   if(lastCell < 5){
    lastCell = 10;
   }
  
    var headerRowRange = sheet.getRange(1, 1, 1, lastCell);
    headerRowRange.setFontWeight('normal');
    headerRowRange.setFontSize(12);
    sheet.setFrozenRows(1);
    sheet.setFrozenColumns(1);
    var connectionMessage = "Your Sheet is Connected";
    spreadsheet.toast(connectionMessage, "Information", 5);
    Logger.log(connectionMessage);
  
    //COLUMN COLOR
    sheet.getRange("A:A").setBackground("#f3f3f3").setFontColor('#AAA');
    sheet.getRange("B:B").setBackground("#f3f3f3").setFontColor('#AAA');
  
  
    //ROW COLOR
    sheet.getRange("1:1").setBackground("#6398eb").setFontColor('#000');
  
    var lastRow = sheet.getLastRow();
    var bgColor = '#f0f0f0';
    if(lastRow < 1000){
      lastRow = 1000;
    }
    for(i=2;i<=lastRow;i++ ){
      if(i % 2){
        sheet.getRange(i + ":" + i).setBackground(bgColor);
      }
      
    }
  }
  
  function doEdit(e){
    var spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = spreadsheet.getActiveSheet();

    var thisTabName = sheet.getName();
    if( YourSheetTabName !== '' && YourSheetTabName !== thisTabName ){
        return;
    }

    var range = e.range;
    var values = range.getValues();
  
  
    // Check if the change affected multiple cells
    if (range.getNumRows() > 1 || range.getNumColumns() > 1) {
      var countRow = 0;
      
      spreadsheet.toast("Wait...", "Please", 6);
      // Browser.msgBox('Notice - Press Ok',"Updating multple, Don't do anything before done", Browser.Buttons.OK);
      var headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
      var allRowObject = {};
      for (var i = 0; i < values.length; i++) {
        for (var j = 0; j < values[i].length; j++) {
          var rowNumber = range.offset(i, j).getRow();
          var colNumber = range.offset(i, j).getColumn();
          var rowData = sheet.getRange(rowNumber, 1, 1, sheet.getLastColumn()).getValues()[0];
  
          var product_id = rowData[0];
          var rowObject = {};
          // rowObject['ID'] = product_id;
          // rowObject['target_col'] = headers[colNumber];
          // rowObject['col_value'] = rowData[colNumber];
          // rowObject['row_number'] = rowNumber;
          // rowObject['col_number'] = colNumber;
  
          for (var k = 0; k < headers.length; k++) {
            var header_title = headers[k];
            header_title=header_title.replace(/ /g,"_");
            rowObject[header_title] = rowData[k];
          }
          
          allRowObject[product_id] = rowObject; 
        }
        countRow++;
        
      }
  
      if( countRow > 1 ){
        spreadsheet.toast("Multiple update started..", "Updating...", 6);
        sendToserver(allRowObject, $website_routespace_multiple);
      }else if( countRow == 1){
        spreadsheet.toast(countRow, "Notice", 16);
        singleCellEdit(e);
      }
      
  
    } else {
      // return;
      singleCellEdit(e);
  
    }
  
    
  }
  
  function singleCellEdit(e){
    var spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    
    // Get the active spreadsheet
    var sheet = spreadsheet.getActiveSheet();
    
    // Get the active cell
    var activeCell = sheet.getActiveCell();
  
    // Get the value of the active cell
    var cellValue = activeCell.getValue();
  
    // Get the row and column of the active cell
    var row = activeCell.getRow();
    var column = activeCell.getColumn();
    
    if(row == 1 || column == 1 || column == 2){
      
      spreadsheet.toast("Unable to change title row, product_id or product_type", "Info", 6);
      try {
        var oldValue = e.oldValue;
        sheet.getRange(row, column).setValue(oldValue);
        return;
      }catch(e){
        return;
      }
      
    }
    spreadsheet.toast("Updating a Product data", "Info", 6);
    var product_id_cell = sheet.getRange(row, 1);
    var product_id = product_id_cell.getValue();
    var title = sheet.getRange(row, 2).getValue();
  
  
  //Getting 1st row's data as header
    var headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
  
    // Get current or last Edit rows all data as value of each keu
    var rowData = sheet.getRange(row, 1, 1, sheet.getLastColumn()).getValues()[0];
  
    // Create an object with headers as keys and row data as values
    var rowObject = {};
    for (var i = 0; i < headers.length; i++) {
      var header_title = headers[i];
      header_title=header_title.replace(/ /g,"_");
      rowObject[header_title] = rowData[i];
    }
  
    // Log the object (you can use it as needed)
    Logger.log(rowObject)
  
  
  
      //Now I will change only title
      var namespanceRout = $website_routespace;
      var url = $website_wpjson + namespanceRout;
      var payload = rowObject;
  
      var product_create_status = false;
      if(product_id=='' && column == 3){
        product_create_status = true;
        spreadsheet.toast("Creating a new Product", "Creating ....", 5);
        
      }else if(product_id==''){
  
        return;
      }
  
      var options = {
        method: "post",
        contentType: "application/json",
        redirect: 'follow',
        payload: JSON.stringify(payload)
      };
  
      // Make the POST request
      // var response = UrlFetchApp.fetch(url, options);
  
      try {
        // Make the POST request
        var response = UrlFetchApp.fetch(url, options);
  
        // Check if the request was successful (status code 2xx)
        if (response.getResponseCode() >= 200 && response.getResponseCode() < 300) {
          if(product_create_status){
            // Show a toast message on success
            spreadsheet.toast("New Product Added", "Information", 5);
          }else{
            // Show a toast message on success
            spreadsheet.toast("Product Update successful!", "Information", 5);
          }
          
        } else {
          // Show an error toast message on non-successful response
          spreadsheet.toast("Error: " + response.getContentText(), "Error", 10);
        }
    } catch (error) {
        // Show an error toast message if an exception occurred
        spreadsheet.toast(url + "\n" + error.message, "DNS Error", 10);
        // spreadsheet.toast("DNS Error: \nYou are unable to upload to a localhost server.\n" + error.message, "DNS Error", 10);
    }
  }
  
  function sendToserver(payload, route){
    var spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    //Now I will change only title
      var namespanceRout = route;
      var url = $website_wpjson + namespanceRout;
  
  
      var options = {
        method: "post",
        contentType: "application/json",
        redirect: 'follow',
        payload: JSON.stringify(payload)
      };
  
      // Make the POST request
      // var response = UrlFetchApp.fetch(url, options);
      Logger.log("started");
      try {
        // Make the POST request
        var response = UrlFetchApp.fetch(url, options);
        
        if (response.getResponseCode() >= 200 && response.getResponseCode() < 300) {
          spreadsheet.toast("Multiple Update Success", "Response Code: " + response.getResponseCode(), 5);
          
        } else {
          // Show an error toast message on non-successful response
          spreadsheet.toast("Error: " + response.getContentText(), "Response Code: " + response.getResponseCode(), 10);
        }
        
    } catch (error) {
        // Show an error toast message if an exception occurred
        spreadsheet.toast("DNS Error: \nYou are unable to upload to a localhost server.\n" + error.message, "DNS Error", 10);
    }
  
  
  }
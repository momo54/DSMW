function test(urlserveur){
    var data = $('.test').val();
    if(data != ''){
        var request = urlserveur+"?title=PushFeed:"+data;
        $("#state").html('<span style=\"margin-left:10px; color:#7CFC00;\">wait</span>');
        jQuery.ajax({
            url: request,
            type: "GET",
            async: true,
            error: function(request) {
                $("#state").html('<span style=\"margin-left:10px; color:\"#DC143C;\"></span>');
            },
            success: function(request) {
                $("#state").html('<span style=\"margin-left:10px; color:#DC143C;\">Warning: this push already exists or was disabled. If you push with this name the former push will be used</span>');
            }
        });
    }
}

function pushpull(urlserveur,name, action)
{
    //$("#T2").html(''); //Clear the Packet list
    $("#state").html('');
    $.post(urlserveur,{
        action: action,
        name: name
    },
    function(data, status) /* On Request Complete */
    {
        //$('#T1').html(data); // put all the data in there
        //$("#state").html(status); // update status
        },
        function(packet,status,fulldata, xhr) /* If the third argument is a function it is used as the OnDataRecieved callback */
        {
            //$("#len").html(fulldata.length); // total how much was recieved so far
            //$("#state").html(status); // status (can be any ajax state or "stream"
            var data = $("#state").html(); // get text of what we received so far
            data += packet; // append the last packet we got
            $("#state").html(data); // update the div
        //$("<li></li>").html(packet).appendTo("#T2"); // add this packet to the list
        }
        );

}

function pushpage(urlserveur,title)
{
    //$("#T2").html(''); //Clear the Packet list
    $("#statepush").html('');
    $('#pushstatus').show();
    /////////////////BEN/////////////////
    $("#undostatus").hide();
    /////////////////BEN/////////////////
    $.post(urlserveur,{
        action: "onpush",
        name: 'PushFeed:PushPage_'+title,
        request: '[['+title+']]',
        page: title
    },
    function(data, status) /* On Request Complete */
    {
        //$('#T1').html(data); // put all the data in there
        //$("#state").html(status); // update status
        },
        function(packet,status,fulldata, xhr) /* If the third argument is a function it is used as the OnDataRecieved callback */
        {
            //$("#len").html(fulldata.length); // total how much was recieved so far
            //$("#state").html(status); // status (can be any ajax state or "stream"
            var data = $("#statepush").html(); // get text of what we received so far
            data += packet; // append the last packet we got
            $("#statepush").html(data); // update the div
        //$("<li></li>").html(packet).appendTo("#T2"); // add this packet to the list
        }
        );

}

/////////////////////////BEN/////////////////////////
function viewtoundopatchs(urlserveur,title)
{
    $("#viewpatchs").show();
    $("#undostatus").hide();
    
    var semanticRequest= [];
    semanticRequest.push('[[Patch:+]]');//we search for patchs
    if(document.forms["formViewUndo"].elements["reqPage"].value != "")
        {
        semanticRequest.push('[[OnPage::'+document.forms["formViewUndo"].elements["reqPage"].value+']]');//on a certain page
        }
    else
        {
        semanticRequest.push('[[OnPage::+]]');
        }
        
    if(document.forms["formViewUndo"].elements["reqDate"].value != "")//if there is a value for the date
        {
        var temp = document.forms["formViewUndo"].elements["reqDate"].value;
        
            if((temp.length > 9)&&(temp.length < 18))//at least as much caracters as in X_May_XXXX wich is the shortest string for a date
                                                     //and not more than XX_September_XXXX wich is the longest
                {
                var tmpDate = '';//will be used to construct the date
                var tmpDateAfter = '';//will be used to construct the day after the previous one

                //we get the elements of the textarea
                var tmpDay = '';
                var i = 0;
                while(temp[i]!=' ')//we get the day
                    {
                    tmpDay += temp[i];
                    i++;
                    }
                var tmpMonth = '';
                for(var j = i+1 ; j<(temp.length - 5) ; j++)//we get the month
                    {
                    tmpMonth += temp[j];
                    }
                var tmpYear = '';    
                for(var k = j+1 ; k<temp.length ; k++)//we get the year
                    {
                    tmpYear += temp[k];
                    }
                                
                if(isValidDay(tmpDay,tmpMonth,tmpYear))
                    {
                    //to obtain the modifications on a precise date
                    //we must use [[Modification date::>date]][[Modification date::<(date+1)]]
                    //as part of the semantic query
                    tmpDate += '>'+tmpDay+' '+tmpMonth+' '+tmpYear;
                    tmpDateAfter += theDayAfter(tmpDay,tmpMonth,tmpYear);
                    tmpDate += ']][[Modification date::<'+tmpDateAfter;
                    }
                else
                    {
                    tmpDate += '+';
                    }
                
                }
        semanticRequest.push('[[Modification date::'+tmpDate+']]');//with its modifications done on a certain day
        }
    else
        {//if the textare is empty
        semanticRequest.push('[[Modification date::+]]');
        }
        
    semanticRequest = JSON.stringify(semanticRequest);
    
    //make a POST wich can't be treated by MediaWiki or Semantic MediaWiki
    $.post(
    urlserveur,{
    action: "onundo",
    name: "",
    page: title,
    req: semanticRequest
    },
    function(data, status) //on request complete
      {},
    function(packet, status, fulldata, xhr) //if the third argument is a function it is used as the OnDataReceived callback
    {
      var data = $("#viewToUndoPatches").html(); //get text of what we received so far
      data += packet; //append the last packet we got
      $("#viewToUndoPatches").html(data); //update the div
    }
    );
    
}

function isValidDay(day,month,year)
{
    var res = false;//result of the function
                
    //conversion of the strings into int
    var dayInt = parseInt(day);    
    var yearInt = parseInt(year);
                               
    //is this day valid (we suppose that years are between 1901 and 2999)
    if((yearInt>1800)&&(yearInt<3000)&&(dayInt>0)&&(dayInt<32))
        {                    
        //month of 31 days
        if((month == "January")||(month == "March")||(month == "May")||(month == "July")||(month == "August")||(month == "October")||(month == "December"))
            {
            res = true;
            }
        else
            {
            //month of 30 only days
            if( ((month == "April")||(month == "June")||(month == "September")||(month == "November")) && (dayInt<31) )
                {
                res = true;
                }
            else
                {
                //in February we must take care of the leap years influnce on it's number of days'
                if( (month == "February") && ( ((yearInt%4 == 0)&&(yearInt%100 != 0)&&(dayInt<30)) || ((yearInt%400 == 0)&&(dayInt<30)) || (dayInt<29)) )
                    {
                    res = true;
                    }
                }
            }
        }

    return res;
}

function theDayAfter(day,month,year)
{
    var res = '';
    var months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    
    //conversions to integer
    var dayInt = parseInt(day);
    var yearInt = parseInt(year);
    
    var i = 0;
    while(months[i]!=month)
        {
        i = i+1;
        }
        
    if(isValidDay((dayInt+1).toString(),months[i],year))//we add 1 to the day and check if it exists
        {
        res += (dayInt+1).toString()+' '+months[i]+' '+year;
        }
    else
        {
        if(isValidDay('01',months[i+1],year))//else the day is the first and we must change the month
            {
            res += '01'+' '+months[i+1]+' '+year;
            }
        else
            {
            if(isValidDay('01',months[0],(yearInt+1).toString()))//else the month is the first and we must change the year
                {
                res += '01'+' '+months[0]+' '+(yearInt+1).toString();
                }
            }
        }
    
    return res;
}

function undopatchs(urlserveur,title,nbMax)
{
  //strongly inspired of allpull()  
    
  //nbMax is the number of patchs displayed on the page
  //$("#stateundo").html('');
  $("#pushstatus").hide();
  $("#undostatus").show();
  var allVals = [];
  
  var i=0;
  var j=0;

  while( i<nbMax )
  {
   if( document.getElementById("check"+i).checked )
   {
     allVals.push( $("#check"+i).val() );
     j = j+1;
   }
   i = i+1;
  }

  allVals = JSON.stringify(allVals);//we must change the structure to communicate it in a shape we can work onto easily

  $.post(
    urlserveur,{
    action: "onundo",
    name: allVals,
    page: title
    },
    function(data, status) //on request complete
      {},
    function(packet, status, fulldata, xhr) //if the third argument is a function it is used as the OnDataReceived callback
    {
      var data = $("#stateundo").html(); //get text of what we received so far
      data += packet; //append the last packet we got
      $("#stateundo").html(data); //update the div
    }
    );
        
}
/////////////////////////BEN/////////////////////////

function allpull(urlserveur)
{
    //$("#T2").html(''); //Clear the Packet list
    $("#statepull").html('');
    $('#pushstatus').hide();
    $('#pullstatus').show();
    var allVals = [];
    $('[name=pull[]]:checked').each(function() {
        allVals.push($(this).val());
    });
    $.post(urlserveur,{
        action: "onpull",
        name: allVals
    },
    function(data, status) /* On Request Complete */
    {
        //$('#T1').html(data); // put all the data in there
        //$("#state").html(status); // update status
        },
        function(packet,status,fulldata, xhr) /* If the third argument is a function it is used as the OnDataRecieved callback */
        {
            //$("#len").html(fulldata.length); // total how much was recieved so far
            //$("#state").html(status); // status (can be any ajax state or "stream"
            var data = $("#statepull").html(); // get text of what we received so far
            data += packet; // append the last packet we got
            $("#statepull").html(data); // update the div
        //$("<li></li>").html(packet).appendTo("#T2"); // add this packet to the list
        }
        );

}

function allpush(urlserveur)
{
    //$("#T2").html(''); //Clear the Packet list
    $("#statepush").html('');
    $('#pullstatus').hide();
    $('#pushstatus').show();
    var allVals = [];
    $('[name=push[]]:checked').each(function() {
        allVals.push($(this).val());
    });
    $.post(urlserveur,{
        action: "onpush",
        name: allVals
    },
    function(data, status) /* On Request Complete */
    {
        //$('#T1').html(data); // put all the data in there
        //$("#state").html(status); // update status
        },
        function(packet,status,fulldata, xhr) /* If the third argument is a function it is used as the OnDataRecieved callback */
        {
            //$("#len").html(fulldata.length); // total how much was recieved so far
            //$("#state").html(status); // status (can be any ajax state or "stream"
            var data = $("#statepush").html(); // get text of what we received so far
            data += packet; // append the last packet we got
            $("#statepush").html(data); // update the div
        //$("<li></li>").html(packet).appendTo("#T2"); // add this packet to the list
        }
        );

}

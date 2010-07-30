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
                $("#state").html('<span style=\"margin-left:10px; color:#DC143C;\">Warning: this push allready exist or was disable. If you push with this name the former push will be use</span>');
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
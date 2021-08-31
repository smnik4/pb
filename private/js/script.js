/*local script*/
$(document).ready(function(){
    $("#index #c1>div").hover(function(e){
        var ct = $(e.currentTarget);
        var i=ct.attr('el');
        $("#c1>div").removeClass('sel');
        ct.addClass('sel');
        $("#d1 .block").addClass('hide');
        $("#d1 .block").removeClass('block');
        $("#d1 #di"+i).addClass('block');
    });
    $("#index").on('keypress','#ac',function(e){
        var ASCIICode = (e.which) ? e.which : e.keyCode
        if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
            return false;
        return true;
    });
    $("#index").on('keyup','#ac',function(e){
        var val=$(e.currentTarget).val();
        console.log(val.length);
        if(val.length === 6){
            location.href = '?auth='+val+'&type=code';
        }
    });
    if($('#index').is('body')){
        var d1h = $('#d1').height();
        $("#d1 div").each(function(i,e){
           var el = $(e);
           var eh = el.height();
           var et = (d1h - eh) / 2;
           el.css('margin-top','calc('+et+'px - 0.5em)');
        });
        //$("#d1 div:first-child").attr('class','block');
    }
});
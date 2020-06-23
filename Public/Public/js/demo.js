

//Regional开始
$(document).ready(function(){
    $(".Regional").click(function(){
        if ($('.grade-eject').hasClass('grade-w-roll')) {
            $('.grade-eject').removeClass('grade-w-roll');
        } else {
            $('.grade-eject').addClass('grade-w-roll');
        }
    });
});

$(document).ready(function(){
    $(".grade-w>li").click(function(){
        $(".grade-t")
            .css("left","33.48%")
    });
});

$(document).ready(function(){
    $(".grade-t>li").click(function(){
        $(".grade-s")
            .css("left","66.96%")
    });
});

//Brand开始

$(document).ready(function(){
    $(".Brand").click(function(){
        if ($('.Category-eject').hasClass('grade-w-roll')) {
            $('.Category-eject').removeClass('grade-w-roll');
        } else {
            $('.Category-eject').addClass('grade-w-roll');
        }
    });
});

$(document).ready(function(){
    $(".Category-w>li").click(function(){
        $(".Category-t")
            .css("left","33.48%")
    });
});

$(document).ready(function(){
    $(".Category-t>li").click(function(){
        $(".Category-s")
            .css("left","66.96%")
    });
});

//Sort开始

// $(document).ready(function(){
//     $(".Sort").click(function(){
//         if ($('.Sort-eject').hasClass('grade-w-roll')) {
//             $('.Sort-eject').removeClass('grade-w-roll');
//         } else {
//             $('.Sort-eject').addClass('grade-w-roll');
//         }
//     });
// });


//判断页面是否有弹出

$(document).ready(function(){
    $(".Regional").click(function(){
        if ($('.Category-eject').hasClass('grade-w-roll')){
            $('.Category-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Regional").click(function(){
        if ($('.Sort-eject').hasClass('grade-w-roll')){
            $('.Sort-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Brand").click(function(){
        if ($('.Sort-eject').hasClass('grade-w-roll')){
            $('.Sort-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Brand").click(function(){
        if ($('.grade-eject').hasClass('grade-w-roll')){
            $('.grade-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Sort").click(function(){
        if ($('.Category-eject').hasClass('grade-w-roll')){
            $('.Category-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Sort").click(function(){
        if ($('.grade-eject').hasClass('grade-w-roll')){
            $('.grade-eject').removeClass('grade-w-roll');
        };

    });
});


//js点击事件监听开始
function grade1(wbj,id,sta,cate){
    var arr = document.getElementById("gradew").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    wbj.style.background = "#eee";
    area_name = $(wbj).text();
    if(id > 0){
        $.post('/shop/home/getAreaChild',{'pid':id},function(res){
            msg = JSON.parse(res.msg);
            if(msg.length>0){
                //清除html内容
                $("#gradet").empty();
                //添加html内容
                for(var i=0;i<msg.length;i++){
                    li = $('<li onclick="gradet(this,'+msg[i]['id']+','+sta+','+cate+')">'+msg[i]['area_name']+'</li>');
                    $("#gradet").append(li);
                }
            }else{
                //清除html内容
                $("#gradet").empty();
                $('.grade-eject').removeClass('grade-w-roll');
                $('.Regional').text(area_name);
                setTimeout(function(){window.location.href="/shop/home/mend/sta/"+sta+"/cate/"+cate+"/area/"+id},500);
            }
        },'json');
    }else{
        //清除html内容
        $("#gradet").empty();
        $('.grade-eject').removeClass('grade-w-roll');
        $('.Regional').text(area_name);
        setTimeout(function(){window.location.href="/shop/home/mend/sta/"+sta+"/cate/"+cate+"/area/"+id},500);
    }
}

function gradet(tbj,id,sta,cate){
    var arr = document.getElementById("gradet").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    tbj.style.background = "#fff"
    city_name = $(tbj).text();
    $('.grade-eject').removeClass('grade-w-roll');
    $('.Regional').text(city_name);
    setTimeout(function(){window.location.href="/shop/home/mend/sta/"+sta+"/cate/"+cate+"/area/"+id},500);

}

// function grades(sbj){
//     var arr = document.getElementById("grades").getElementsByTagName("li");
//     for (var i = 0; i < arr.length; i++){
//         var a = arr[i];
//         a.style.borderBottom = "";
//     };
//     sbj.style.borderBottom = "solid 1px #ff7c08"
// }

function Categorytw(wbj,id,sta,area){

    var arr = document.getElementById("Categorytw").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    wbj.style.background = "#eee";

    cate_name = $(wbj).text();
    if(id > 0){
        $.post('/shop/home/getCateChild',{'pid':id},function(res){
            msg = JSON.parse(res.msg);
            if(msg.length>0){
                //清除html内容
                $("#Categoryt").empty();
                //添加html内容
                for(var i=0;i<msg.length;i++){
                    li = $('<li onclick="Categoryt(this,'+msg[i]['id']+','+sta+','+area+')">'+msg[i]['name']+'</li>');
                    $("#Categoryt").append(li);
                }
            }else{
                $("#Categoryt").empty();
                $('.Category-eject').removeClass('grade-w-roll');
                $('.Brand').text(cate_name);
                setTimeout(function(){window.location.href="/shop/home/mend/sta/"+sta+"/cate/"+id+"/area/"+area},500);
                
            }
        },'json');
    }else{
        $("#Categoryt").empty();
        $('.Category-eject').removeClass('grade-w-roll');
        $('.Brand').text(cate_name);
        setTimeout(function(){window.location.href="/shop/home/mend/sta/"+sta+"/cate/"+id+"/area/"+area},500);
    }
}

function sale_num(wbj,id,sta,area,sale_num){
    if(sale_num==1){
        window.location.href="/shop/home/mend/sta/"+sta+"/cate/"+id+"/area/"+area+"/sale_num/0";
    }else{
        window.location.href="/shop/home/mend/sta/"+sta+"/cate/"+id+"/area/"+area+"/sale_num/1";
    }
}

function Categoryt(tbj,id,sta,area){
    var arr = document.getElementById("Categoryt").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    tbj.style.background = "#fff"
    cate_name = $(tbj).text();
    $('.Category-eject').removeClass('grade-w-roll');
    $('.Brand').text(cate_name);
    setTimeout(function(){window.location.href="/shop/home/mend/sta/"+sta+"/cate/"+id+"/area/"+area},500);
}

// function Categorys(sbj){
//     var arr = document.getElementById("Categorys").getElementsByTagName("li");
//     for (var i = 0; i < arr.length; i++){
//         var a = arr[i];
//         a.style.borderBottom = "";
//     };
//     sbj.style.borderBottom = "solid 1px #ff7c08"
// }

// function Sorts(sbj){
//     var arr = document.getElementById("Sort-Sort").getElementsByTagName("li");
//     for (var i = 0; i < arr.length; i++){
//         var a = arr[i];
//         a.style.borderBottom = "";
//     };
//     sbj.style.borderBottom = "solid 1px #ff7c08"
// }

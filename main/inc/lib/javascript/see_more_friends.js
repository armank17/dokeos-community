function moreFriends(name_search){

    var $loadingGif = $('#loading-gif');
    var $seeMore     = $('#see_more');
    var start = $('#friends-wp .image-social-content').length;

    if(name_search == undefined || name_search == '' ){
        $.ajax({
            url: 'more_result_friends.php',
            type: 'get',
            data: 'start=' + start,
            dataType: "json",
            success: function(data){               
                add_friends(data.friends);
                $('#loading-gif').hide();

                if( data.remainder > 0){
                    $('#see_more').show();
                }else
                {
                    $('#see_more').hide();
                    $('#loading-gif').hide();
                }
            },
            error: function(){
            console.log('Fail to conect to servidor')
            }
        });
    }
    else{
        $.ajax({
            contentType: "application/x-www-form-urlencoded",
            type: "POST",
            url: 'more_result_friends.php?a=show_my_friends',
            data: 'search_name_q='+name_search,
            success: function(data){
                $('#see_more').hide();
                $("div#friends-wp").html(data);

            },
            complete:function(data){
                $('#loading-gif').hide();
            },
            error: function(){
                console.log('Fail to conect to servidor')
            }
        });
    }      	
}

function add_friends(list_friends){

    var $container_main = $('#friends-wp');
    for( var i = 0; i < list_friends.length; i++ ){
        var $image             = $('<img src="" title="" id="test" style="height:60px;border:3pt solid #eee">');
        var $imgDelete         = $('<img onclick="delete_friend(this)"  src="../img/blank.gif" alt="" title=""  class="image-delete" />')
        var $link              = $('<a href=""></a>');
        var $firstName         = $('<div></div>');
        var $lastName          = $('<div></div>');
        var $friends           = $('<div onmouseout="hide_icon_delete(this)" onmouseover="show_icon_delete(this)" ></div>');
        var $photo             = $('<center> </center>');		
        var $names             = $('<center class="friend"></center>');


        $friends.addClass('image-social-content');

        $image.attr('id', 'imgfriend_'+list_friends[i].friend_user_id);
        $image.attr('title', list_friends[i].firstName+' '+list_friends[i].lastName);
        $image.attr('src', list_friends[i].image);
        $friends.attr('id','div_'+list_friends[i].friend_user_id);
        $imgDelete.attr('id', 'img_'+list_friends[i].friend_user_id);
        $link.attr('href', 'profile.php?u='+list_friends[i].friend_user_id);

        $firstName.text( list_friends[i].firstName );
        $lastName.text( list_friends[i].lastName );

        $names.append($firstName);
        $names.append($lastName);
        $link.append($image);     
        $photo.append($link);
        $link.append($firstName);
        $link.append($lastName);
        $friends.append($photo);
        $friends.append($imgDelete);
        $friends.append($names);
        $container_main.append($friends);                 
    }       
}

$(document).ready(function(){
    moreFriends();
    $('#see_more').hide();
});
$('#see_more').click(function(e){
    $('#see_more').hide();
    $('#loading-gif').show();
    e.preventDefault();
    moreFriends();
});
function search_image_socialx(element_html)  {
    $('#loading-gif').show();
    $('#see_more').hide();
    var $container_main = $('.image-social-content');
    $container_main.detach();

    var name_search;
    name_search=$(element_html).attr("value");       
    moreFriends(name_search);
}
function cleancont(){
    var $container_main = $('.image-social-content');
    $container_main.detach();
}
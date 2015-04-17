/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    urls = '';
    for(var i = 3; i < hashes.length; i++)
    {
//        hash = hashes[i].split('=');
        urls += hashes[i] + '&';
//        vars.push(hash[0]);
//        vars[hash[0]] = hash[1];
    }
    urls = urls.substring(0, urls.length - 1);
    return urls;
}

//Mobile - index
$('#list-index').live('pageshow', function(event) {
    $('#course-id').html('');
    procesa='index.php?module=mobile&cmd=index&func=json&case=course';
    $.getJSON(procesa, 
        function(json){
            $.each(json, function(i,item){
                
                $('#course-id').append('<li><a href="index.php?module=mobile&cmd=panel&func=index&course=' + 
                    json[i].course_code + '&session=' + json[i].session +'">' + json[i].title +
                    '</a></li>'
                );
            });
            
            $('#course-id').listview('refresh');
        });
    
    $('#session-id').html('');
    procesa='index.php?module=mobile&cmd=index&func=json&case=session';
    $.getJSON(procesa, 
        function(json){
            //Session
            /*
             * <li><a href="index.html">
             * <img src="images/gf.png" alt="France" class="ui-li-icon ui-corner-none">
             * France <span class="ui-li-count">4</span></a></li>
             */
            $.each(json, function(i,item){
                $('#session-id').append('<li><a href="index.php?module=mobile&cmd=index&func=course&session=' + json[i].id_session + '">' +
                    '<img src="application/mobile/assets/images/reporting.png" alt="Temp" class="ui-li-icon ui-corner-none">' +
                    json[i].name +
                    '<span class="ui-li-count">' + json[i].course_count + '</span></a>' +
                    '</li>'
                );
                $('#session-id').listview('refresh');
            });
            
        });
    return false;
});

$('#list-course').live('pageshow', function(event) {
    var urls = getUrlVars();
    if(urls){
        $('#course-ids').html('');
    
        procesa='index.php?module=mobile&cmd=index&func=json&case=sessionCourse&'+urls;
        $.getJSON(procesa, 
            function(json){
                $.each(json, function(i,item){
                    $('#course-ids').append('<li><a href="index.php?module=mobile&cmd=panel&func=index&course=' + 
                        json[i].course_code + '&session=' + json[i].id_session +'">' + json[i].title + 
                        '</a></li>'
                    );
                });
                $('#course-ids').listview('refresh');
            });
        return false;
    }
});

$('#list-announ').live('pageshow', function(event) {
    var urls = getUrlVars();
    if(urls){
        $('#announ-id').html('');
    
        procesa='index.php?module=mobile&cmd=announcements&func=json&case=announ&'+urls;
        $.getJSON(procesa, 
            function(json){
                $.each(json, function(i,item){
                    $('#announ-id').append('<li><a href="index.php?module=mobile&cmd=announcements&func=details&id=' + 
                        json[i].id + '&course=' + json[i].course + '"><h3>' + json[i].title + '</h3><p>' + json[i].content + '</p>' +
                        '<p class="ui-li-aside">' + json[i].end_date + '</p>' +
                        '</a></li>'
                    );

                });

                $('#announ-id').listview('refresh');
            });
            return false;
        }
        Announ_jsonAnoun(id);
});


$('#list-detail').live('pageshow', function(event) {
    var id = getUrlVars();
    if(id)
        Announ_jsonDetail(id);
});

function Announ_jsonDetail(urls){
    procesa='index.php?module=mobile&cmd=announcements&func=json&case=detail&'+urls;
    $.getJSON(procesa, 
        function(json){
            $.each(json, function(i,item){
                $('#announ-title').text(json.title);
                $('#announ-date').text(json.end_date);
                $('#announ-content').text(json.content);
            });
            
            $('#details-id').listview('refresh');
        });
        return false;
}




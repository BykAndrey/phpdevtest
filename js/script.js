
var currentPage=1;
var max_page=1;

$(document).ready(function(){
    loadList();
    /*Send message by ajax*/
    $( "#form" ).submit(function( event ) {
        console.log( "Handler for .submit() called." );
        event.preventDefault();
        var text=$(this).children('textarea').eq(0).val();

        $.ajax({
            url:'/',
            method:'post',
            data:{
                'ajax':'create',
                'addsms':'Отправить',
                'save':1,
                'TEXT':text
            },
            success:function(data){
                console.log('Response: '+data);
                loadList(currentPage);
            }
        });

        $(this).children('textarea').eq(0).val('');

      });

   
      
});


function prevPage(){
    currentPage--;
    if(currentPage<1){
        currentPage=1;
    }
    $('#currentPage').html(currentPage);

    loadList(currentPage);
}

function nextPage(){
    currentPage++;

    if(currentPage>max_page){
        currentPage=max_page;
    }
    $('#currentPage').html(currentPage);
    loadList(currentPage);
}

function DropMessage(id){
    $.ajax({
        url:'/',
        method:'get',
        data:{
            'ajax':'dropMessage',
            'id':id
            
        },
        success:function(data){
           loadList(currentPage);
        }
    });
  }



function loadList(page=1){

    $.ajax({
        url:'/',
        method:'get',
        data:{
            'ajax':'getList',
            'page':page
            
        },
        success:function(data){
            $('div#list').html('');
         


            console.log(data);
            if(data['list']!=null ){
                console.log(2);
                for(var i=0;i<=data['list'].length-1;i++){
                    $('div#list').append("<p>"+data['list'][i]['ID']+"-"+data['list'][i]['TEXT']+" <b onclick='DropMessage("+data['list'][i]['ID']+")'>Удалить</b></p>");
                }
        }

            
            
            max_page=data['max_page'];


            if(currentPage>max_page){
                currentPage=max_page;
                loadList(currentPage);
            }
            $('#currentPage').html(currentPage);
        }
    });
  }

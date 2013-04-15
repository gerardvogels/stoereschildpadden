$(document).ready(function() {
    $('a.confirm').click(function(e) {
        e.preventDefault();
        var message = $(this).attr('data:confirmation'); // <a href="/delete/id/3" class="confirm" data:confirmation="Weet u zeker dat u dit wilt verwijderen?">Blabla</a>
        if (confirm(message)) {
            document.location.href=$(this).attr('href');
        }
    });
});
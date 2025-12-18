$(document).ready(function () {

    carregarResultados(1);

    function carregarResultados(pagina) {
        $.ajax({
            url: 'resultados_ajax.php',
            type: 'GET',
            data: { pagina: pagina },
            success: function (data) {
                $('#resultado').html(data);
            }
        });
    }

    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        let pagina = $(this).data('pagina');
        carregarResultados(pagina);
    });

});

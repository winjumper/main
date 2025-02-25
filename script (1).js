jQuery(document).ready(function($) {
    $('#ad-diagnosis-form').submit(function(e) {
        e.preventDefault();

        var symptoms = $('#symptoms').val();
        var carMake = $('#car-make').val();
        var carModel = $('#car-model').val();

        $.ajax({
            url: ad_params.ajax_url,
            method: 'POST',
            data: {
                action: 'ad_process_diagnosis',
                symptoms: symptoms,
                car_make: carMake,
                car_model: carModel
            },
            success: function(response) {
                if (response.success) {
                    // Используем html() вместо text()
                    $('#result').html('Диагностика: ' + response.data.diagnosis);

                    var articles = response.data.articles;
                    if (articles.length > 0) {
                        var links = articles.map(function(article) {
                            return '<a href="' + article.link + '" target="_blank">' + article.title + '</a>';
                        }).join('<br>');
                        $('#articles').html('Полезные статьи:<br>' + links);
                    } else {
                        $('#articles').html('Нет статей для данного запроса.');
                    }

                    // Отображаем уточняющий вопрос, если он есть
                    if (response.data.follow_up) {
                        $('#follow-up').html('Уточнение: ' + response.data.follow_up);
                    } else {
                        $('#follow-up').html('');
                    }
                } else {
                    $('#result').text('Ошибка: ' + response.data.message);
                }
            },
            error: function() {
                $('#result').text('Произошла ошибка при обработке запроса.');
            }
        });
    });
});

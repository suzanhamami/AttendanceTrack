{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
{{--
    Dynamic search (jQuery + Ajax, no page reload) shared by the exams grid and the results grid.
    Expects: $form (css selector of the filter form) and $grid (css selector of the grid container).
    The server returns only the table fragment when the request is an Ajax request.
--}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    (function ($) {
        var $form = $(@json($form));
        var $grid = $(@json($grid));
        var timer = null;

        function load(url, data) {
            $grid.addClass('opacity-50');

            $.ajax({ url: url, data: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .done(function (html) {
                    $grid.html(html);
                    // keep the address bar in sync so the filtered list can be bookmarked
                    var query = data ? '?' + data : '';
                    window.history.replaceState(null, '', data ? $form.attr('action') + query : url);
                })
                .fail(function () {
                    $grid.html('<div class="alert alert-danger m-3">Could not load the results. Please try again.</div>');
                })
                .always(function () { $grid.removeClass('opacity-50'); });
        }

        // search while typing (debounced) and when a filter changes
        $form.on('input change', 'input, select', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { load($form.attr('action'), $form.serialize()); }, 300);
        });

        $form.on('submit', function (e) {
            e.preventDefault();
            load($form.attr('action'), $form.serialize());
        });

        // pagination links also load through Ajax
        $grid.on('click', '.pagination a', function (e) {
            e.preventDefault();
            load($(this).attr('href'));
        });
    })(jQuery);
</script>

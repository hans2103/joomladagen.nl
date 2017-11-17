/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

!function($){
    $(document).ready(function(){

        joomlamailerJS.create_2_5 = {
            init: function() {

                $.each($('div.accordion-heading'), function() {
                    if (!$(this).next('div.accordion-body').hasClass('in')) {
                        $(this).find('a.accordion-toggle').addClass('collapsed');
                    }
                });

                $('.accordion div.accordion-body:not(.in)').slideUp();

            }
        }

        $('a.accordion-toggle').click(function(e) {
            e.preventDefault();
            if ($(this).hasClass('collapsed')) {
                $(this).removeClass('collapsed');
                var body = $(this).closest('div.accordion-group').find('div.accordion-body');
                body.slideDown().addClass('in');

                $('a.accordion-toggle').not(this).addClass('collapsed');
                $('div.accordion-body').not(body).slideUp();

                $('#activeArticleListSlider').val($(this).attr('href').replace(/^#/, ''));
            }
        });

        joomlamailerJS.create_2_5.init();
    });
}(jQuery);

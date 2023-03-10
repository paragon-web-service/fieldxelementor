"use strict";

(function ($) {
  $(document).ready(function () {
    $('.stm_lms_categories_dropdown__childs').each(function () {
      $(this).before('<span class="stm_lms_cat_toggle"></span>');
    });
    $('.header_main_menu_wrapper .sub-menu').each(function () {
      $(this).before('<span class="stm_lms_menu_toggle"></span>');
    });
    $('body').on('click', '.stm_lms_cat_toggle', function (e) {
      $(this).parent().find('.stm_lms_categories_dropdown__childs').slideToggle();
    });
    $('body').on('click', '.stm_lms_menu_toggle', function (e) {
      $(this).closest('li').find('.sub-menu').toggleClass('active');
    });
    /*Open Popups*/

    /*Account Popup*/

    $('.stm_header_top_toggler').on('click', function () {
      createBg();
      $('body').toggleClass('mobile_logo_modal');
      $('.header_mobile div:first').addClass('stm_lms_mobile_header_open');
      $('.header_mobile').addClass('header-mobile-trigger');

      if ($('#header').hasClass('sticky_header')) {
        $('#header').removeClass('sticky_header').addClass('was_sticky').css('padding-bottom', '0');
      }

      $('.stm_lms_header_popups_overlay').addClass('active');
      $('.stm_lms_account_popup').toggleClass('active');

      if ($('body').hasClass('rtl')) {
        $('.stm_header_top_toggler').hide();
      }
    });
    $('.stm_lms_account_popup__close').on('click', function () {
      $('body').toggleClass('mobile_logo_modal');
      $('.stm_lms_header_popups_overlay, .stm_lms_account_popup').removeClass('active');
      $('.header_mobile div:first').removeClass('stm_lms_mobile_header_open');
      $('.header_mobile').removeClass('header-mobile-trigger');

      if ($('body').hasClass('rtl')) {
        $('.stm_header_top_toggler').show();
      }

      if ($('#header').hasClass('was_sticky')) {
        $('#header').addClass('sticky_header');
      }
    });
    /*Search Popup*/

    $('.stm_header_top_search').on('click', function () {
      createBg();
      $('body').toggleClass('mobile_logo_modal');
      $('.stm_lms_header_popups_overlay').addClass('active');
      $('.header_mobile div:first').addClass('stm_lms_mobile_header_open');
      $('.header_mobile').addClass('header-mobile-trigger');
      $('.stm_lms_search_popup').toggleClass('active');

      if ($('body').hasClass('rtl')) {
        $('.stm_header_top_toggler').hide();
      }

      if ($('#header').hasClass('sticky_header')) {
        $('#header').removeClass('sticky_header').addClass('was_sticky').css('padding-bottom', '0');
      }
    });
    $('.stm_lms_search_popup__close').on('click', function () {
      $('body').toggleClass('mobile_logo_modal');
      $('.stm_lms_header_popups_overlay, .stm_lms_search_popup').removeClass('active');
      $('.header_mobile div:first').removeClass('stm_lms_mobile_header_open');
      $('.header_mobile').removeClass('header-mobile-trigger');

      if ($('body').hasClass('rtl')) {
        $('.stm_header_top_toggler').show();
      }

      if ($('#header').hasClass('was_sticky')) {
        $('#header').addClass('sticky_header');
      }
    });
    /*Menu Popup*/

    $('.stm_lms_categories-courses__toggler').on('click', function () {
      createBg();
      $('.stm_lms_header_popups_overlay').addClass('active');
      $('.categories-courses').toggleClass('active');
    });
    $('.stm_menu_toggler').on('click', function () {
      createBg();
      $('.stm_lms_header_popups_overlay').addClass('active');
      $('.stm_lms_menu_popup').toggleClass('active');
    });
    $('.stm_lms_menu_popup__close').on('click', function () {
      $('.stm_lms_header_popups_overlay, .stm_lms_menu_popup').removeClass('active');
    });
    /*Overlay Close*/

    $(document).on('click', '.stm_lms_header_popups_overlay', function () {
      $('.categories-courses, .stm_lms_header_popups_overlay, .stm_lms_account_popup, .stm_lms_search_popup, .stm_lms_menu_popup').removeClass('active');
    });

    function createBg() {
      if (!$('.stm_lms_header_popups_overlay').length) {
        $('.header_default').append('<div class="stm_lms_header_popups_overlay"></div>');
      }
    }
  });
})(jQuery);
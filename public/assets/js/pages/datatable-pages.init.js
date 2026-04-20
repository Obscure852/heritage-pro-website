/******/
(function () { // webpackBootstrap
  var __webpack_exports__ = {};
  /*!****************************************************!*\
    !*** ./resources/js/pages/datatable-pages.init.js ***!
    \****************************************************/
  /*
  Template Name: Minia - Admin & Dashboard Template
  Author: Themesbrand
  Website: https://themesbrand.com/
  Contact: themesbrand@gmail.com
  File: datatable for pages Js File
  */
  // datatable
  $(document).ready(function () {
    $('.datatable').DataTable({
      responsive: false,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"]
      ],
    });
    $(".dataTables_length select").addClass('form-select form-select-sm');
  });
  /******/
})();
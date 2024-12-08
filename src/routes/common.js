import $ from 'jquery';
window.jQuery = $;

export default {
  init() {
    // JavaScript to be fired on all pages

    // Navigation Toggle
    $('#nav-toggle').click(e => {
      e.preventDefault();
      $(e.currentTarget).toggleClass('is-active');
      $('#nav-primary').toggleClass('is-active');
    });
  },
  finalize() {
    // JavaScript to be fired on all pages, after init JS is fired
  },
};

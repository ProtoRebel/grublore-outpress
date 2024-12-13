import $ from 'jquery';
window.jQuery = $;

export default {
  init() {
  },
  finalize() {
    // State Options
    let mealActive = 0;
    let dishActive = 0;
    let alertAction = '';

    // Reused Items
    const appContent = $('#content');
    const mealsList = $('#meals');
    const mealTemplate = $('#meal-template');
    const mealNameInput = $('#meal-name');
    const alertBox = $('#alert');
    const alertConfirm = $('#alert-confirm');
    const alertCancel = $('#alert-cancel');

    // Util: Generate random 6 digit code
    function mealIdGen() {
      const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
      let randomCode = '';

      for (let i = 0; i < 6; i++) {
        const randomIndex = Math.floor(Math.random() * characters.length);
        randomCode += characters.charAt(randomIndex);
      }

      return randomCode;
    }

    // Alert: Throw confirmations
    function alertThrow(action,heading, message, confirm, cancel) {
      appContent.addClass('is-alert');
      alertBox.find('h1').text(heading);
      $('#alert-message').html(message);
      alertBox.find('h1').text(heading);
      alertConfirm.text(confirm);
      alertCancel.find('em').text(cancel);
      alertAction = action;
    }

    function alertClear() {
      appContent.removeClass('is-alert');
      alertBox.find('h1').text();
      $('#alert-message').html();
      alertBox.find('h1').text();
      alertConfirm.text();
      alertCancel.text();
      alertAction = '';
    }

    // Meal: Update localStorage single value at a time
    function mealStorageUpdate(mealId, key, newValue) {
      const storedData = localStorage.getItem('gl-meal_' + mealId);

      if (storedData) {
        const dataObj = JSON.parse(storedData);
        dataObj[key] = newValue;
        localStorage.setItem('gl-meal_' + mealId, JSON.stringify(dataObj));
      }
    }

    // Meal: Find localStorage items and create entries
    function mealsStorageLoad() {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);

        if (key.startsWith('gl-meal_')) {
          const newId = key.replace('gl-meal_', '');
          const mealNewItem = mealTemplate.clone();
          const mealNewItemName = JSON.parse(localStorage.getItem(key))['name'];
          mealNewItem.attr('id', newId);
          mealNewItem.find('h2').text(mealNewItemName);
          mealsList.append(mealNewItem);
        }
      }
    }
    mealsStorageLoad();

    // Meal: Add New Function
    function mealNew() {
      const mealNewItem = mealTemplate.clone();
      const mealNewId = mealIdGen();
      mealNewItem.attr('id', mealNewId);
      localStorage.setItem(`gl-meal_${mealNewId}`, '{"name":"Unnamed Meal","dishes":[]}');
      mealsList.append(mealNewItem);
      mealActive = mealNewId;
    }

    // Meal: Add Button Click
    $('#meal-add').click(() => {
      mealNew();
      const mealName = $(`#${mealActive}`).find('h2').text();
      mealNameInput.text(mealName);
      appContent.addClass('is-meal');
    });

    // Meal: View Meal on List Item click
    mealsList.on('click', '.meal-listing', (e) => {
      const mealId = $(e.currentTarget).attr('id');
      const mealName = $(e.currentTarget).find('h2').text();
      mealActive = mealId;
      mealNameInput.text(mealName);
      appContent.addClass('is-meal');
    });

    // Meal: Close View Function
    function mealClose() {
      mealActive = 0;
      appContent.removeClass('is-meal');
      mealNameInput.text();
    }

    // Meal: Close View Button
    $('#layer-meal .layer-close').click((e) => {
      e.preventDefault();
      mealClose();
    });

    // Meal: Update Name
    mealNameInput.on('keyup', (e) => {
      const mealNameUpdate = $(e.currentTarget).text();
      mealStorageUpdate(mealActive, 'name', mealNameUpdate);
      $(`#${mealActive}`).find('h2').text(mealNameUpdate);
    });

    // Meal: Remove
    $('#meal-remove').click(e => {
      e.preventDefault();
      const mealName = $(`#${mealActive}`).find('h2').text();

      alertThrow('mealRemove', 'Whoa!', `You are about to remove the meal <strong>${mealName}</strong> and its dishes and notes.`, 'Remove Meal', 'Keep Meal');

    });

    // Alert: Actions
    alertConfirm.click(e => {
      e.preventDefault();
      if(alertAction === 'mealRemove') {
        $(`#${mealActive}`).remove();
        localStorage.removeItem(`gl-meal_${mealActive}`);
        mealClose();
        mealActive = 0;
        appContent.removeClass('is-meal');
      }
      alertClear();
    });
    alertCancel.click(e => {
      alertClear();
    });
  },
};

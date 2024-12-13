import $ from 'jquery';
window.jQuery = $;

export default {
  init() {
  },
  finalize() {
    // Reused Items
    const appContent = $('#content');
    const mealsList = $('#meals');
    const mealTemplate = $('#meal-template');
    const mealNameInput = $('#meal-name');
    const alertBox = $('#alert');
    const alertConfirm = $('#alert-confirm');
    const alertCancel = $('#alert-cancel');
    const mealDishesEmpty = 'No dishes have been added to this meal';

    // State: Variables
    let mealActive = '';
    let dishActive = '';
    let alertAction = '';
    let glState = JSON.parse(localStorage.getItem('gl-state'));

    // State: Pull on load
    function glStateLoad() {
      if(glState !== null) {
        mealActive = glState['meal'];
        dishActive = glState['dish'];
        appContent.addClass(glState['display']);
        mealDataLoad(mealActive);
      }
    }
    glStateLoad();

    // State: Update Function
    function glStateUpdate() {
      const stateDisplay = appContent.attr('class');
      localStorage.setItem('gl-state', `{"display": "${stateDisplay}", "meal": "${mealActive}", "dish": "${dishActive}"}`);
    }

    // State: Update on every click
    document.addEventListener('click', event => {
      glStateUpdate();
    });

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
      alertBox.find('h1').text('');
      $('#alert-message').html('');
      alertBox.find('h1').text('');
      alertConfirm.text('');
      alertCancel.find('em').text('');
      alertAction = '';
    }

    // Meal: Construct Dishes
    function mealDishes(dishes, returnType) {
      dishes = typeof dishes === 'string' ? dishes : String(dishes);
      if(dishes.trim() === '') {
        if(mealActive.length > 0) {
          $(`#${mealActive}`).find('em').text(mealDishesEmpty);
        }
      } else {
        const dishesArray = dishes.split(',');
        let listMealDishes = [];
        let mealDishes = [];
        dishesArray.forEach(dish => {
          const dishData = $(`#${dish}`);
          const dishName = dishData.find('h1').text();
          const dishCourse = dishData.find('.course').text();
          listMealDishes.push(dishName);
          mealDishes.push({id: dish, name: dishName, course: dishCourse});
        });
        if(returnType === 'short') {
          return listMealDishes.join(' + ');
        } else if(returnType === 'list') {
          return 'should be a list here';
        }
      }
    }
    function mealListDishes() {
      $('.meal-listing').each((i,e) => {
        const dishesList = $(e).data('dishes');
        const dishesListSpace = $(e).find('strong');
        if(dishesList > 0 || dishesList.length > 0) {
          dishesListSpace.text(mealDishes(dishesList, 'short'));
        } else {
          dishesListSpace.text('');
          dishesListSpace.next('em').text(mealDishesEmpty);
        }
      });
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
      mealListDishes();
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

    // Meal: Load Data into Layer
    function mealDataLoad(mealId) {
      const meal = $(`#${mealId}`);
      const mealName = meal.find('h2').text();
      const dishesList = meal.data('dishes');
      mealActive = mealId;
      mealNameInput.text(mealName);
      mealDishes(dishesList, 'list');
      appContent.addClass('is-meal');
    }

    // Meal: View Meal on List Item click
    mealsList.on('click', '.meal-listing', (e) => {
      mealDataLoad($(e.currentTarget).attr('id'));
    });

    // Meal: Close View Function
    function mealClose() {
      mealActive = 0;
      appContent.removeClass('is-meal');
      mealNameInput.text('');
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

    // Meal: Add Dish Button
    $('#dish-add').click(e => {
      appContent.addClass('is-select');
    });

    // Dish: Populate Details Function
    function dishDetails(dishId) {
      dishActive = dishId;
      appContent.addClass('is-dish');
      appContent.removeClass('is-select');
      $('.dish').removeClass('is-active');
      $(`#${dishId}`).addClass('is-active');
    }

    // Dish: View from List
    $('.dish').click(e => {
      const dishTarget = $(e.currentTarget).attr('id');
      if(appContent.hasClass('is-select')) {
        dishDetails(dishTarget);
        appContent.addClass('is-preview')
      }
    });

    // Dish: Navigate Between Dishes
    $('.dish .dish-link').click(e => {
      e.preventDefault();
      const dishTarget = $(e.currentTarget).attr('href').replace('#', '');
      dishDetails(dishTarget);
    });

    // State: Pull on load
    function glStateLoad() {
      if(glState !== null) {
        mealActive = glState['meal'];
        dishActive = glState['dish'];
        let displayFull =  glState['display'];
        const displayTrim = ' is-alert';
        if (displayFull.indexOf(displayTrim) !== -1) {
          displayFull = displayFull.replace(displayTrim, '').trim();
        }
        if(mealActive.length > 0) {
          mealDataLoad(mealActive);
        }
        if(dishActive.length > 0) {
          dishDetails(dishActive);
        }
        appContent.attr('class', displayFull);
      }
    }
    glStateLoad();

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

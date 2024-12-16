import $ from 'jquery';
window.jQuery = $;

export default {
  init() {
  },
  finalize() {
    /*
    ----------
    App Elements & Globals
    ----------
     */

    // App - Elements
    const elContent = $('#content');
    const elMealsList = $('#meals');
    const elMealTemplate = $('#meal-template');
    const elMealAdd = $('#meal-add');
    const elMealName = $('#meal-name');
    const elMealRemove = $('#meal-remove');
    const elMealDishes = $('#meal-dishes');
    const elMealDishTemplate = $('#meal-dish-template');
    const elMealNote = $('#meal-note');
    const elDishAdd = $('#dish-add');
    const elDishGeneral = $('.dish');
    const elControl = $('#control');
    const elControlSelect = $('#control-select');
    const elControlPreview = $('#control-preview');
    const elControlDishAdd = $('#control-preview-add');
    const elControlDishAdded = $('#control-preview-added');
    const elAlert = $('#alert');
    const elAlertMessage = $('#alert-message');
    const elAlertConfirm = $('#alert-confirm');
    const elAlertCancel = $('#alert-cancel');

    // App - Strings
    const elMealEmptyName = 'Unnamed Meal';
    const elMealEmptyNote = '• No dishes have been added to this meal';

    // Util - Function: Current UNIX Timestamp
    function unixTimestamp() {
      return Math.floor(Date.now() / 1000);
    }

    /*
    ----------
    State Management
    ----------
     */

    // State - Variables: Initiate
    let stateLayer = '';
    let stateMeal = '';
    let stateDish = '';
    let stateControl = '';
    let stateAlert = '';
    let glState = JSON.parse(localStorage.getItem('gl-state'));

    // State - Action: Create on Load

    // State - Function: Create
    function statePopulate() {
      if(glState && typeof glState === 'object') {
        stateLayer = String(glState['layer'] || '');
        stateMeal = String(glState['meal'] || '');
        stateDish = String(glState['dish'] || '');
        stateControl = String(glState['control'] || '');
        elContent.attr('class', stateLayer);
        mealPopulate(stateMeal);
      } else {
        stateUpdate();
      }
    }
    statePopulate();

    // State - Function: Update
    // NOTE: Run this function anytime a State Var is updated
    function stateUpdate() {
      stateLayer = elContent.attr('class').replace(' is-alert', '');
      localStorage.setItem('gl-state', `{"layer":"${stateLayer}","meal":"${stateMeal}","dish":"${stateDish}","control":"${stateControl}"}`);
    }


    /*
    ----------
    List
    ----------
     */

    // List - Function: Populate the List
    function listPopulate() {
      elMealsList.children().not('#meal-template').remove();
      Object.keys(localStorage).forEach((key) => {
        if(key.startsWith('gl-meal_')) {
          const meal = JSON.parse(localStorage.getItem(key));
          if(meal) {
            const $newMeal = elMealTemplate.clone();
            const mealId = key.replace('gl-meal_', '');
            $newMeal.attr('id', mealId);
            $newMeal.find('h2').text(meal.name);
            $newMeal.find('p strong').text(dishName(meal.dishes) || '');
            $newMeal.find('p em').text(Array.isArray(meal.dishes) && (meal.dishes).length > 0 ? meal.note : `${meal.note} ${elMealEmptyNote}`);
            elMealsList.append($newMeal);
          }
        }
      });
    }
    listPopulate();

    // List - Function: Clone & Add Meal
    function listCreate() {
      const newMealId = unixTimestamp();
      localStorage.setItem(`gl-meal_${newMealId}`, `{"name":"${elMealEmptyName}","dishes":[],"note":""}`);
      stateMeal = String(newMealId);
      stateUpdate();
      controlInit();
      listPopulate();
    }

    // List - Action: Add Meal
    elMealAdd.click(e => {
      listCreate();
      elContent.addClass('is-meal');
      elMealName.text(elMealEmptyName);
      stateUpdate();
    });

    // List - Action: View Meal
    elMealsList.on('click', '.meal-listing', (e) => {
      stateMeal = $(e.currentTarget).attr('id');
      elContent.addClass('is-meal');
      stateUpdate();
      mealPopulate(stateMeal);
      // TODO: Add focus() to title, highlight text
    });


    /*
    ----------
    Meal
    ----------
     */

    // Meal - Function: Fetch Meal data
    function mealData(meal) {
      if(meal.trim() !== '') {
        const mealStorage = localStorage.getItem('gl-meal_' + meal);
        try {
          JSON.parse(mealStorage);
          const mealStorageObj = JSON.parse(mealStorage);
          const mealName = mealStorageObj['name'];
          const mealDishes = mealStorageObj['dishes'];
          const mealNote = mealStorageObj['note'];
          return {name: mealName, dishes: mealDishes, note: mealNote};
        } catch {
          return false;
        }
      }
    }

    // Meal - Function: Update Meal Data
    function mealUpdate(meal, key, newValue) {
      const storedData = localStorage.getItem('gl-meal_' + meal);
      if(storedData) {
        const dataObj = JSON.parse(storedData);
        dataObj[key] = newValue;
        localStorage.setItem('gl-meal_' + meal, JSON.stringify(dataObj));
      }
      stateUpdate();
    }

    // Meal - Function: Populate the Details
    function mealPopulate(meal) {
      if(meal.trim() !== '') {
        const mealDataObj = mealData(meal);
        elMealName.text(mealDataObj['name']);
        elMealDishes.find('li').not('#meal-dish-template').remove();
        const mealDishes = mealData(meal)['dishes'];
        if(mealDishes.length > 0) {
          mealDishes.forEach(e => {
            const currentDishEl = $(`#${e}`);
            const $newMealDish = elMealDishTemplate.clone().removeAttr('id');
            $newMealDish.attr('data-dish', e);
            const dishPhoto = currentDishEl.find('.photo').clone();
            $newMealDish.prepend(dishPhoto);
            $newMealDish.find('strong').text(dishName([e]));
            const dishCourse = currentDishEl.find('.course').text();
            $newMealDish.find('em').text(dishCourse);
            elMealDishes.append($newMealDish);
          });
        }
      }
      stateUpdate();
    }
    mealPopulate(stateMeal);

    // Meal - Action: Update Meal Name
    elMealName.on('keyup', e => {
      const mealNameUpdate = $(e.currentTarget).text();
      mealUpdate(stateMeal, 'name', mealNameUpdate);
      $(`#${stateMeal}`).find('h2').text(mealNameUpdate);
    });

    // Meal - Function: Close Details
    function mealClose() {
      stateMeal = '';
      elContent.removeClass('is-meal');
      elMealDishes.find('li').not('#meal-dish-template').remove();
      stateUpdate();
      listPopulate();
    }

    // Meal - Action: Add Dish
    elDishAdd.click(e => {
      elContent.addClass('is-select');
      stateControl = 'select';
      controlInit();
      stateUpdate();
    });

    // Meal - Action: Close Details
    $('#meal .layer-close').click(e => {
      e.preventDefault();
      mealClose();
    });

    // Meal - Action: Update Meal Note
    elMealNote.on('keyup', e => {
      const mealNoteUpdate = $(e.currentTarget).val();
      mealUpdate(stateMeal, 'note', mealNoteUpdate);
      $(`#${stateMeal}`).find('p em').text(mealNoteUpdate);
    });


    // Meal - Function: Remove Dish from Meal
    function mealDishRemove(dish, meal) {
      const mealDishes = mealData(meal)['dishes'];
      const updatedDishes = mealDishes.filter(num => num !== parseInt(dish));
      mealUpdate(meal, 'dishes', updatedDishes);
      mealPopulate(meal);
    }

    // Meal - Action: Remove Dish from Meal
    elMealDishes.on('click', '.meal-dish-remove', e => {
      e.stopPropagation();
      e.preventDefault();
      const dishToRemove = $(e.currentTarget).parent('li').attr('data-dish');
      stateDish = dishToRemove;
      alertRemoveDishFromMeal(dishToRemove, stateMeal);
    });

    // Meal - Function: Remove Meal
    function mealRemove(meal) {
      stateMeal = '';
      localStorage.removeItem(`gl-meal_${meal}`);
      elContent.removeClass('is-meal');
      stateUpdate();
      listPopulate();
    }

    // Meal - Action: Remove Meal
    elMealRemove.click(e => {
      alertRemoveMeal(stateMeal);
    });

    /*
    ----------
    Dish
    ----------
     */

    // Dish - Function: Fetch dish name (look for comma, then split if needed)
    function dishName(dish) {
      if(Array.isArray(dish)) {
        let dishNames = [];
        dish.forEach(e => {
          const dishName = $(`#${e}`).find('h1').text();
          dishNames.push(dishName);
        });
        return dishNames.join(' + ');
      }
    }

    // Dish - Function: Open Details
    function dishOpen(dish) {
      if(dish.length > 0) {
        elDishGeneral.each((i,e) => {
          $(e).removeClass('is-active');
        });
        stateDish = dish;
        elContent.addClass('is-dish');
        $(`#${dish}`).addClass('is-active');
        stateUpdate();
      }
    }
    dishOpen(stateDish);

    // Dish - Action: View Details
    elMealDishes.on('click', 'li', (e) => {
      const dishTarget = $(e.currentTarget).attr('data-dish');
      dishOpen(dishTarget);
      // TODO: Add focus() to title, highlight text
    });

    // Dish - Action: Navigate to related Dish
    $('.dish-link').click(e => {
      const dishLink = $(e.currentTarget).attr('href').replace('#', '');
      dishOpen(dishLink);
      controlInit();
    });

    // Dish - Function: Close Details
    function dishClose() {
      elContent.removeClass('is-dish');
      stateDish = '';
      elDishGeneral.each((i,e) => {
        $(e).removeClass('is-active');
      });
      stateUpdate();
    }

    // Dish - Action: Close view with Button
    $('#dishes .layer-close').click(e => {
      e.preventDefault();
      dishClose();
    });

    // Dish - Action: Load Preview
    elDishGeneral.click(e => {
      if(elContent.hasClass('is-select')) {
        const selectedDish = $(e.currentTarget).attr('id');
        elContent.removeClass('is-select');
        elContent.addClass('is-preview');
        stateControl = 'preview';
        dishOpen(selectedDish);
        stateUpdate();
        controlInit();
      }
    });

    /*
    ----------
    Control
    ----------
     */

    // Control - Function: Display Controls
    function controlInit() {
      elControl.find('nav').removeClass('is-active');
      elControl.find('p').show();
      elControl.find('button').show();
      if(stateControl === 'select') {
        elControlSelect.find('p strong').text(mealData(stateMeal)['name']);
        elControlSelect.addClass('is-active');
      } else if(stateControl === 'preview') {
        elControlPreview.find('p strong').text(mealData(stateMeal)['name']);
        elControlPreview.addClass('is-active');
        if(mealData(stateMeal)['dishes'].includes(parseInt(stateDish))) {
          elControlDishAdd.hide();
        } else {
          elControlDishAdded.hide();
        }
      }
    }
    controlInit();

    // Control - Action: Back from Select
    $('#control-select-back').click(e => {
      stateControl = '';
      elContent.removeClass('is-select');
      stateUpdate();
      controlInit();
    });

    // Control - Action: Back from Preview
    $('#control-preview-back').click(e => {
      stateControl = 'select';
      stateDish = '';
      elContent.removeClass('is-dish is-preview');
      elContent.addClass('is-select');
      stateUpdate();
      controlInit();
    });

    // Control - Action: Add Dish to Meal
    elControlDishAdd.click(e => {
      const currentMealList = mealData(stateMeal)['dishes'];
      currentMealList.push(parseInt(stateDish));
      mealUpdate(stateMeal, 'dishes', currentMealList);
      controlInit();
      mealPopulate(stateMeal);
      listPopulate();
    });


    /*
    ----------
    Alert
    ----------
     */

    // Alert - Function: Throw Alert
    function alertThrow(action, heading, message, confirm, cancel) {
      elContent.addClass('is-alert');
      elAlert.find('h1').text(heading);
      elAlertMessage.html(message);
      elAlertConfirm.text(confirm);
      elAlertCancel.find('em').text(cancel);
      stateAlert = action;
    }
    function alertClear() {
      elContent.removeClass('is-alert');
      elAlert.find('h1').text('');
      elAlertMessage.html('');
      elAlertConfirm.text('');
      elAlertCancel.find('em').text('');
      stateAlert = '';
    }

    // Alert - Action: Cancel/Clear Action
    elAlertCancel.click(e => {
      alertClear();
    });

    // Alert - Function: Remove Dish from Meal
    function alertRemoveDishFromMeal(dish, meal) {
      const message = `You are about to remove <strong>${dishName([dish])}</strong> from the meal <strong>${mealData(meal)['name']}</strong>.`;
      alertThrow('dishRemove', 'Warning!', message, 'Remove Dish', 'Keep Dish');
    }

    // Alert - Function: Remove Meal
    function alertRemoveMeal(meal) {
      const message = `You are about to remove the meal <strong>${mealData(meal)['name']}</strong> and all the dishes inside it.`
      alertThrow('mealRemove', 'Whoa!', message, 'Remove Meal', 'Keep Meal');
    }

    // Alert - Action: Grouped Button Actions
    elAlertConfirm.click(e => {
      if(stateAlert === 'dishRemove') {
        mealDishRemove(stateDish, stateMeal);
        stateDish = '';
        mealPopulate(stateMeal);
        listPopulate();
      } else if(stateAlert === 'mealRemove') {
        mealRemove(stateMeal);
      }
      elContent.removeClass('is-alert');
      stateUpdate();
    });
  },
};

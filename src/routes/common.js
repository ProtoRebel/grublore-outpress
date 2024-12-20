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



    /* Known Working




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
    let controlAction = '';
    let alertAction = '';
    let glState = JSON.parse(localStorage.getItem('gl-state'));

    // State: Pull on load
    function glStateLoad() {
      if(glState !== null) {
        mealActive = glState['meal'];
        dishActive = glState['dish'];
        controlAction = glState['control'];
        appContent.addClass(glState['display']);
        mealDataLoad(mealActive);
      }
    }
    glStateLoad();

    // State: Update Function
    function glStateUpdate() {
      const stateDisplay = appContent.attr('class');
      localStorage.setItem('gl-state', `{"display": "${stateDisplay}", "meal": "${mealActive}", "dish": "${dishActive}", "control": "${controlAction}"}`);
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

    // Control: Toggle Drawer
    function controlToggle(control) {
      controlAction = control;
      $('#control nav').removeClass('is-active');
      $(`#control-${control}`).addClass('is-active');
      const mealFetch = $(`#${mealActive}`);
      const mealDishes = mealFetch.data('dishes');
      const mealName = mealFetch.find('h2').text();
      if(control === 'select') {
        $('#control-select').find('p strong').text(mealName);
      } else if(control === 'preview') {
        if(mealDishes !== undefined) {
          mealDishes.split(',');
          if(mealDishes.includes(dishActive)) {
            $('#control-preview-add').hide();
            $('#control-preview-added strong').text(mealName);
          } else {
            $('#control-preview-added').hide();
          }
        }
      }
    }

    // Control: Clean Out
    function controlClean() {
      controlAction = '';
      $('#control *').removeClass('is-active');
      $('#control-preview-add, #control-preview-added').show();
      $('#control-preview-added strong').text('');
    }

    // Control: Preview Back
    $('#control-preview-back').click(e => {
      appContent.removeClass('is-preview is-dish');
      appContent.addClass('is-select');
      controlClean();
      controlToggle('select');
    });

    // Control: Select Back
    $('#control-select-back').click(e => {
      appContent.removeClass('is-select');
      controlClean();
    });

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

    // Dish: Update the List function
    function dishMealListBuild() {
      return;
    }

    // Meal: Find localStorage items and create entries
    function mealsStorageLoad() {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);

        if (key.startsWith('gl-meal_')) {
          const newId = key.replace('gl-meal_', '');
          const mealNewItem = mealTemplate.clone();
          const mealNewItemName = JSON.parse(localStorage.getItem(key))['name'];
          const mealNewItemDishes = JSON.parse(localStorage.getItem(key))['dishes'];
          mealNewItem.attr('id', newId);
          mealNewItem.find('h2').text(mealNewItemName);
          mealNewItem.data('dishes', mealNewItemDishes);
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
      localStorage.setItem(`gl-meal_${mealNewId}`, '{"name":"Unnamed Meal","dishes":""}');
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
      controlToggle('select');
    });

    // Dish: Populate Details Function
    function dishDetails(dishId) {
      dishActive = dishId;
      appContent.addClass('is-dish');
      appContent.removeClass('is-select');
      $('.dish').removeClass('is-active');
      $(`#${dishId}`).addClass('is-active');
      $('html, body').scrollTop(0);
    }

    // Dish: View from List
    $('.dish').click(e => {
      const dishTarget = $(e.currentTarget).attr('id');
      if(appContent.hasClass('is-select')) {
        dishDetails(dishTarget);
        appContent.addClass('is-preview');
        controlToggle('preview');
      }
    });

    // Dish: Navigate Between Dishes
    $('.dish .dish-link').click(e => {
      e.preventDefault();
      const dishTarget = $(e.currentTarget).attr('href').replace('#', '');
      dishDetails(dishTarget);
      controlClean();
      if(appContent.hasClass('is-preview')) {
        controlToggle('preview');
      }
    });

    // Dish: Add to Meal Function
    function dishAdd() {
      const mealActiveElem = $(`#${mealActive}`);
      let mealDishes = mealActiveElem.data('dishes');
      const mealDishesCurrent = mealDishes.split(',');
      controlClean();
      console.log();
      if(mealDishesCurrent.includes(dishActive)) {
        mealDishesCurrent.push(dishActive);
        mealDishes = mealDishesCurrent.join(',');
        mealStorageUpdate(mealActive,'dishes', mealDishes);
        mealActiveElem.attr('data-dishes', mealDishes);
      }
    }

    // Dish: Add from Preview
    $('#control-preview-add').click(e => {
      dishAdd();
      controlClean();
      controlToggle('preview');
    });

    // State: Pull on load
    function glStateLoad() {
      if(glState !== null) {
        mealActive = glState['meal'];
        dishActive = glState['dish'];
        controlAction = glState['control'];
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
        if(controlAction.length > 0) {
          controlToggle(controlAction);
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
     */
  },
};

import $ from 'jquery';
window.jQuery = $;

/**
 * Recipe and Meal Planning Application
 *
 * This application manages recipes and meal plans with browser-based storage.
 * Features include:
 * - Creating and managing meals
 * - Adding/removing dishes to meals
 * - Persistent storage using localStorage
 * - Notes and annotations for meals
 */
export default {
  init() {
  },
  finalize() {
    // DOM Elements
    const elements = {
      content: $('#content'),
      meals: {
        list: $('#meals'),
        template: $('#meal-template'),
        addButton: $('#meal-add'),
        nameField: $('#meal-name'),
        removeButton: $('#meal-remove'),
        dishes: $('#meal-dishes'),
        dishTemplate: $('#meal-dish-template'),
        noteField: $('#meal-note')
      },
      dishes: {
        addButton: $('#dish-add'),
        general: $('.dish'),
        closeButton: $('#dishes .layer-close')
      },
      control: {
        main: $('#control'),
        select: $('#control-select'),
        preview: $('#control-preview'),
        dishAdd: $('#control-preview-add'),
        dishAdded: $('#control-preview-added'),
        backButtons: {
          select: $('#control-select-back'),
          preview: $('#control-preview-back')
        }
      },
      alert: {
        container: $('#alert'),
        message: $('#alert-message'),
        confirm: $('#alert-confirm'),
        cancel: $('#alert-cancel')
      },
      layers: {
        all: $('.layer'),
        list: $('#layer-list'),
        meal: $('#layer-meal'),
        dish: $('#layer-dish'),
        control: $('#layer-control'),
        alert: $('#layer-alert')
      }
    };

    // Constants
    const CONSTANTS = {
      MEAL_EMPTY_NAME: 'Unnamed Meal',
      MEAL_EMPTY_NOTE: '• No dishes have been added to this meal',
      STORAGE_PREFIX: 'gl-meal_',
      STATE_KEY: 'gl-state',
      LAYER_CLASSES: {
        MEAL: 'is-meal',
        DISH: 'is-dish',
        SELECT: 'is-select',
        PREVIEW: 'is-preview',
        ALERT: 'is-alert'
      }
    };

    // State Management
    let state = {
      layer: '',
      meal: '',
      dish: '',
      control: '',
      alert: ''
    };

    /**
     * Utility Functions
     */
    const utils = {
      unixTimestamp: () => Math.floor(Date.now() / 1000),

      getMealStorageKey: (mealId) => `${CONSTANTS.STORAGE_PREFIX}${mealId}`,

      formatDishNames: (dishes) => {
        if (!Array.isArray(dishes)) return '';
        return dishes.map(id => $(`#${id}`).find('h1').text()).join(' + ');
      }
    };

    /**
     * Alert Management
     */
    const alertManager = {
      throw(action, heading, message, confirm, cancel) {
        elements.content.addClass(CONSTANTS.LAYER_CLASSES.ALERT);
        elements.alert.container.find('h1').text(heading);
        elements.alert.message.html(message);
        elements.alert.confirm.text(confirm);
        elements.alert.cancel.find('em').text(cancel);
        state.alert = action;
      },

      clear() {
        elements.content.removeClass(CONSTANTS.LAYER_CLASSES.ALERT);
        elements.alert.container.find('h1').text('');
        elements.alert.message.html('');
        elements.alert.confirm.text('');
        elements.alert.cancel.find('em').text('');
        state.alert = '';
      },

      removeDishFromMeal(dish, meal) {
        const mealData = mealManager.getData(meal);
        const dishName = utils.formatDishNames([dish]);
        const message = `You are about to remove <strong>${dishName}</strong> from the meal <strong>${mealData.name}</strong>.`;
        this.throw('dishRemove', 'Warning!', message, 'Remove Dish', 'Keep Dish');
      },

      removeMeal(meal) {
        const mealData = mealManager.getData(meal);
        const message = `You are about to remove the meal <strong>${mealData.name}</strong> and all the dishes inside it.`;
        this.throw('mealRemove', 'Whoa!', message, 'Remove Meal', 'Keep Meal');
      }
    };

    /**
     * Control Panel Management
     */
    const controlManager = {
      init() {
        elements.control.main.find('nav').removeClass('is-active');
        elements.control.main.find('p').show();
        elements.control.main.find('button').show();

        const mealData = mealManager.getData(state.meal);
        if (!mealData) return;

        if (state.control === 'select') {
          elements.control.select.find('p strong').text(mealData.name);
          elements.control.select.addClass('is-active');
        } else if (state.control === 'preview') {
          elements.control.preview.find('p strong').text(mealData.name);
          elements.control.preview.addClass('is-active');

          if (mealData.dishes.includes(parseInt(state.dish))) {
            elements.control.dishAdd.hide();
            elements.control.dishAdded.show();
          } else {
            elements.control.dishAdd.show();
            elements.control.dishAdded.hide();
          }
        }
      },

      clear() {
        elements.control.main.find('nav').removeClass('is-active');
        state.control = '';
      }
    };

    /**
     * Layer Management
     */
    const layerManager = {
      clearAll() {
        elements.content.removeClass([
          CONSTANTS.LAYER_CLASSES.MEAL,
          CONSTANTS.LAYER_CLASSES.DISH,
          CONSTANTS.LAYER_CLASSES.SELECT,
          CONSTANTS.LAYER_CLASSES.PREVIEW,
          CONSTANTS.LAYER_CLASSES.ALERT
        ].join(' '));
        controlManager.clear();
      },

      showMeal() {
        this.clearAll();
        elements.content.addClass(CONSTANTS.LAYER_CLASSES.MEAL);
      },

      showDish() {
        elements.content.addClass(CONSTANTS.LAYER_CLASSES.DISH);
      },

      showSelect() {
        this.clearAll();
        elements.content.addClass(CONSTANTS.LAYER_CLASSES.SELECT);
        state.control = 'select';
        controlManager.init();
      },

      showPreview() {
        this.clearAll();
        elements.content.addClass(CONSTANTS.LAYER_CLASSES.PREVIEW);
        state.control = 'preview';
        controlManager.init();
      },

      closeLayer(layerClass) {
        elements.content.removeClass(layerClass);
        if (layerClass === CONSTANTS.LAYER_CLASSES.MEAL) {
          state.meal = '';
        } else if (layerClass === CONSTANTS.LAYER_CLASSES.DISH) {
          state.dish = '';
          elements.dishes.general.removeClass('is-active');
        }
        controlManager.clear();
        stateManager.update();
      }
    };

    /**
     * State Management Functions
     */
    const stateManager = {
      load() {
        const savedState = JSON.parse(localStorage.getItem(CONSTANTS.STATE_KEY));
        if (savedState && typeof savedState === 'object') {
          state = {
            layer: String(savedState.layer || ''),
            meal: String(savedState.meal || ''),
            dish: String(savedState.dish || ''),
            control: String(savedState.control || ''),
            alert: String(savedState.alert || '')
          };

          // Set initial classes
          elements.content.attr('class', state.layer);

          // Initialize appropriate views based on state
          if (state.meal) {
            mealManager.populate(state.meal);
          }

          if (state.dish) {
            elements.dishes.general.removeClass('is-active');
            $(`#${state.dish}`).addClass('is-active');
          }

          // Initialize control panel if needed
          if (state.control) {
            controlManager.init();
          }
        } else {
          this.update();
        }
      },

      update() {
        state.layer = elements.content.attr('class').replace(' is-alert', '');
        localStorage.setItem(CONSTANTS.STATE_KEY, JSON.stringify(state));
      }
    };

    /**
     * List Management Functions
     */
    const listManager = {
      populate() {
        elements.meals.list.children().not('#meal-template').remove();

        Object.keys(localStorage).forEach(key => {
          if (key.startsWith(CONSTANTS.STORAGE_PREFIX)) {
            const meal = JSON.parse(localStorage.getItem(key));
            if (meal) {
              const $newMeal = elements.meals.template.clone();
              const mealId = key.replace(CONSTANTS.STORAGE_PREFIX, '');
              $newMeal.attr('id', mealId);
              $newMeal.find('h2').text(meal.name);
              $newMeal.find('p strong').text(utils.formatDishNames(meal.dishes) || '');
              $newMeal.find('p em').text(
                  Array.isArray(meal.dishes) && meal.dishes.length > 0
                      ? meal.note
                      : `${meal.note} ${CONSTANTS.MEAL_EMPTY_NOTE}`
              );
              elements.meals.list.append($newMeal);
            }
          }
        });
      }
    };

    /**
     * Meal Management Functions
     */
    const mealManager = {
      getData(mealId) {
        if (!mealId?.trim()) return null;
        try {
          const storage = localStorage.getItem(utils.getMealStorageKey(mealId));
          return JSON.parse(storage);
        } catch {
          return null;
        }
      },

      update(mealId, key, newValue) {
        const data = this.getData(mealId);
        if (data) {
          data[key] = newValue;
          localStorage.setItem(utils.getMealStorageKey(mealId), JSON.stringify(data));
          stateManager.update();
        }
      },

      populate(mealId) {
        if (!mealId?.trim()) return;

        const mealData = this.getData(mealId);
        if (!mealData) return;

        // Update name and clear existing dishes
        elements.meals.nameField.text(mealData.name);
        elements.meals.noteField.val(mealData.note);
        elements.meals.dishes.find('li').not('#meal-dish-template').remove();

        // Populate dishes
        if (mealData.dishes?.length) {
          mealData.dishes.forEach(dishId => {
            const dishEl = $(`#${dishId}`);
            const newDish = elements.meals.dishTemplate.clone().removeAttr('id');
            newDish.attr('data-dish', dishId);

            const photo = dishEl.find('.photo').clone();
            newDish.prepend(photo);
            newDish.find('strong').text(utils.formatDishNames([dishId]));
            newDish.find('em').text(dishEl.find('.course').text());

            elements.meals.dishes.append(newDish);
          });
        }

        stateManager.update();
      }
    };

    /**
     * Event Handlers
     */
    function bindEvents() {
      window.addEventListener('load', () => {
        if (elements.content.hasClass(CONSTANTS.LAYER_CLASSES.DISH)) {
          if (state.dish) {
            // Reset dish view properly
            elements.dishes.general.removeClass('is-active');
            $(`#${state.dish}`).addClass('is-active');

            if (state.control === 'preview') {
              layerManager.showPreview();
              layerManager.showDish();
            } else {
              layerManager.showDish();
            }

            // Scroll to the correct dish
            $(`#${state.dish}`)[0].scrollIntoView({ behavior: 'auto', block: 'start' });
          } else {
            // If we have no dish state but dish layer is showing, clean up
            elements.content.removeClass(CONSTANTS.LAYER_CLASSES.DISH);
            elements.dishes.general.removeClass('is-active');
          }
        }
      });

      // Alert Events
      elements.alert.cancel.on('click', () => {
        alertManager.clear();
      });

      elements.alert.confirm.on('click', () => {
        if (state.alert === 'dishRemove') {
          const mealDishes = mealManager.getData(state.meal).dishes;
          const updatedDishes = mealDishes.filter(id => id !== parseInt(state.dish));
          mealManager.update(state.meal, 'dishes', updatedDishes);
          state.dish = '';
          mealManager.populate(state.meal);
          listManager.populate();
        } else if (state.alert === 'mealRemove') {
          localStorage.removeItem(utils.getMealStorageKey(state.meal));
          state.meal = '';
          layerManager.closeLayer(CONSTANTS.LAYER_CLASSES.MEAL);
          listManager.populate();
        }
        alertManager.clear();
        stateManager.update();
      });

      // Close buttons
      $('.layer-close').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        // For any dish view, ensure complete cleanup
        if (elements.content.hasClass(CONSTANTS.LAYER_CLASSES.DISH)) {
          elements.dishes.general.removeClass('is-active');
          state.dish = '';

          // If we're in preview mode, go back to select
          if (state.control === 'preview') {
            elements.content.removeClass(CONSTANTS.LAYER_CLASSES.DISH);
            layerManager.showSelect();
          } else {
            // Otherwise just close the dish view completely
            elements.content.removeClass(CONSTANTS.LAYER_CLASSES.DISH);
          }

          stateManager.update();
          return;
        }

        // For other layers
        const layerClass = Object.values(CONSTANTS.LAYER_CLASSES)
            .find(className => elements.content.hasClass(className));

        if (layerClass) {
          layerManager.closeLayer(layerClass);
        }
      });

      // Meal List Events
      elements.meals.list.on('click', '.meal-listing', function(e) {
        state.meal = $(this).attr('id');
        layerManager.showMeal();
        stateManager.update();
        mealManager.populate(state.meal);
      });

      elements.meals.addButton.on('click', () => {
        const newMealId = utils.unixTimestamp();
        const initialMeal = {
          name: CONSTANTS.MEAL_EMPTY_NAME,
          dishes: [],
          note: ''
        };

        localStorage.setItem(
            utils.getMealStorageKey(newMealId),
            JSON.stringify(initialMeal)
        );

        state.meal = String(newMealId);
        layerManager.showMeal();
        elements.meals.nameField.text(CONSTANTS.MEAL_EMPTY_NAME);
        stateManager.update();
        listManager.populate();
      });

      // Meal Name Update
      elements.meals.nameField.on('keyup', function(e) {
        const newName = $(this).text();
        mealManager.update(state.meal, 'name', newName);
        $(`#${state.meal}`).find('h2').text(newName);
      });

      // Meal Note Update
      elements.meals.noteField.on('keyup', function(e) {
        const newNote = $(this).val();
        mealManager.update(state.meal, 'note', newNote);
        $(`#${state.meal}`).find('p em').text(newNote);
      });

      // Remove Meal
      elements.meals.removeButton.on('click', () => {
        alertManager.removeMeal(state.meal);
      });

      // Remove Dish from Meal
      elements.meals.dishes.on('click', '.meal-dish-remove', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dishToRemove = $(this).closest('li').attr('data-dish');
        state.dish = dishToRemove;
        alertManager.removeDishFromMeal(dishToRemove, state.meal);
      });

      // View Dish from Meal
      elements.meals.dishes.on('click', 'li', function(e) {
        if (!$(e.target).hasClass('meal-dish-remove')) {
          const dishId = $(this).attr('data-dish');
          state.dish = dishId;
          elements.dishes.general.removeClass('is-active');
          $(`#${dishId}`).addClass('is-active');
          layerManager.showDish();
          // Scroll dish into view
          $(`#${dishId}`)[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
          stateManager.update();
        }
      });

      // Dish Selection during Add
      elements.dishes.general.on('click', function(e) {
        if (elements.content.hasClass(CONSTANTS.LAYER_CLASSES.SELECT)) {
          elements.dishes.general.removeClass('is-active');

          const selectedDish = $(this).attr('id');
          state.dish = selectedDish;
          $(this).addClass('is-active');

          elements.content.removeClass(CONSTANTS.LAYER_CLASSES.DISH);

          layerManager.showPreview();
          layerManager.showDish();
          // Scroll dish into view
          $(this)[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
          stateManager.update();
        }
      });

      // Add Dish Button
      elements.dishes.addButton.on('click', () => {
        // Clear dish state when starting dish selection
        state.dish = '';
        elements.dishes.general.removeClass('is-active');
        layerManager.showSelect();
      });

      // Control Back Buttons
      elements.control.backButtons.select.on('click', () => {
        state.dish = '';
        elements.dishes.general.removeClass('is-active');
        layerManager.closeLayer(CONSTANTS.LAYER_CLASSES.SELECT);
        layerManager.showMeal();
        // Update meal view to show new dishes
        mealManager.populate(state.meal);
      });

      elements.control.backButtons.preview.on('click', () => {
        state.dish = '';
        elements.dishes.general.removeClass('is-active');
        layerManager.closeLayer(CONSTANTS.LAYER_CLASSES.DISH);
        layerManager.showSelect();
      });

      // Add Dish to Meal
      elements.control.dishAdd.on('click', () => {
        const mealData = mealManager.getData(state.meal);
        if (mealData) {
          const dishes = mealData.dishes || [];
          dishes.push(parseInt(state.dish));
          mealManager.update(state.meal, 'dishes', dishes);

          // Instead of closing and showing meal, just update control bar
          controlManager.init();

          // Update the meal list in the background
          listManager.populate();
        }
      });
    }

    /**
     * Initialize Application
     */
    const initialize = () => {
      stateManager.load();
      bindEvents();
      listManager.populate();
    };

    // Start the application
    initialize();
  }
};
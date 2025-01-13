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
     * Control Panel Management
     */
    const controlManager = {
      init() {
        elements.control.main.find('nav').removeClass('is-active');
        elements.control.main.find('p').show();
        elements.control.main.find('button').show();

        if (state.control === 'select') {
          elements.control.select.find('p strong').text(mealManager.getData(state.meal)?.name || '');
          elements.control.select.addClass('is-active');
        } else if (state.control === 'preview') {
          elements.control.preview.find('p strong').text(mealManager.getData(state.meal)?.name || '');
          elements.control.preview.addClass('is-active');

          // Handle "Add to Meal" button visibility
          if (mealManager.getData(state.meal)?.dishes?.includes(parseInt(state.dish))) {
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
     * Utility Functions
     */
    const utils = {
      unixTimestamp: () => Math.floor(Date.now() / 1000),

      /**
       * Gets a meal's storage key
       * @param {string} mealId - The meal identifier
       * @returns {string} Storage key for the meal
       */
      getMealStorageKey: (mealId) => `${CONSTANTS.STORAGE_PREFIX}${mealId}`,

      /**
       * Formats dish names for display
       * @param {Array} dishes - Array of dish IDs
       * @returns {string} Formatted dish names
       */
      formatDishNames: (dishes) => {
        if (!Array.isArray(dishes)) return '';
        return dishes.map(id => $(`#${id}`).find('h1').text()).join(' + ');
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
            control: String(savedState.control || '')
          };
          elements.content.attr('class', state.layer);
          mealManager.populate(state.meal);
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
      // Close buttons
      $('.layer-close').on('click', function(e) {
        e.preventDefault();
        const $parentLayer = $(this).closest('.layer');
        const layerClass = Object.values(CONSTANTS.LAYER_CLASSES)
            .find(className => elements.content.hasClass(className));

        if (layerClass) {
          layerManager.closeLayer(layerClass);
          // If we're closing a dish view, go back to select view
          if (layerClass === CONSTANTS.LAYER_CLASSES.DISH && state.control === 'preview') {
            layerManager.showSelect();
          }
        }
      });

      // Meal List Events
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


      // Meal Listing Click
      elements.meals.list.on('click', '.meal-listing', function(e) {
        state.meal = $(this).attr('id');
        layerManager.showMeal();
        stateManager.update();
        mealManager.populate(state.meal);
      });

      // Control Back Buttons
      elements.control.backButtons.select.on('click', () => {
        layerManager.closeLayer(CONSTANTS.LAYER_CLASSES.SELECT);
        layerManager.showMeal();
      });

      elements.control.backButtons.preview.on('click', () => {
        layerManager.closeLayer(CONSTANTS.LAYER_CLASSES.PREVIEW);
        layerManager.showSelect();
      });

      // Dish Selection
      elements.dishes.general.on('click', function(e) {
        if (elements.content.hasClass(CONSTANTS.LAYER_CLASSES.SELECT)) {
          const selectedDish = $(this).attr('id');
          state.dish = selectedDish;
          $(this).addClass('is-active');
          layerManager.showPreview();
          layerManager.showDish();
          stateManager.update();
        }
      });

      // Add Dish Button
      elements.dishes.addButton.on('click', () => {
        layerManager.showSelect();
      });

      // Control Back Buttons
      elements.control.backButtons.select.on('click', () => {
        layerManager.closeLayer(CONSTANTS.LAYER_CLASSES.SELECT);
        layerManager.showMeal();
      });

      elements.control.backButtons.preview.on('click', () => {
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
          layerManager.closeLayer(CONSTANTS.LAYER_CLASSES.PREVIEW);
          layerManager.showMeal();
          mealManager.populate(state.meal);
          listManager.populate();
        }
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
        // Implement meal removal logic
        localStorage.removeItem(utils.getMealStorageKey(state.meal));
        state.meal = '';
        elements.content.removeClass('is-meal');
        stateManager.update();
        listManager.populate();
      });

      // Add Dish
      elements.dishes.addButton.on('click', () => {
        elements.content.addClass('is-select');
        state.control = 'select';
        stateManager.update();
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
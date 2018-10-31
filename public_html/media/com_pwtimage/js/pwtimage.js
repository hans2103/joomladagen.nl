/*!
 * Cropper.js v1.0.0
 * https://github.com/fengyuanchen/cropperjs
 *
 * Copyright (c) 2017 Fengyuan Chen
 * Released under the MIT license
 *
 * Date: 2017-09-03T12:52:44.102Z
 */

(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
	typeof define === 'function' && define.amd ? define(factory) :
	(global.Cropper = factory());
}(this, (function () { 'use strict';

var DEFAULTS = {
  // Define the view mode of the cropper
  viewMode: 0, // 0, 1, 2, 3

  // Define the dragging mode of the cropper
  dragMode: 'crop', // 'crop', 'move' or 'none'

  // Define the aspect ratio of the crop box
  aspectRatio: NaN,

  // An object with the previous cropping result data
  data: null,

  // A selector for adding extra containers to preview
  preview: '',

  // Re-render the cropper when resize the window
  responsive: true,

  // Restore the cropped area after resize the window
  restore: true,

  // Check if the current image is a cross-origin image
  checkCrossOrigin: true,

  // Check the current image's Exif Orientation information
  checkOrientation: true,

  // Show the black modal
  modal: true,

  // Show the dashed lines for guiding
  guides: true,

  // Show the center indicator for guiding
  center: true,

  // Show the white modal to highlight the crop box
  highlight: true,

  // Show the grid background
  background: true,

  // Enable to crop the image automatically when initialize
  autoCrop: true,

  // Define the percentage of automatic cropping area when initializes
  autoCropArea: 0.8,

  // Enable to move the image
  movable: true,

  // Enable to rotate the image
  rotatable: true,

  // Enable to scale the image
  scalable: true,

  // Enable to zoom the image
  zoomable: true,

  // Enable to zoom the image by dragging touch
  zoomOnTouch: true,

  // Enable to zoom the image by wheeling mouse
  zoomOnWheel: true,

  // Define zoom ratio when zoom the image by wheeling mouse
  wheelZoomRatio: 0.1,

  // Enable to move the crop box
  cropBoxMovable: true,

  // Enable to resize the crop box
  cropBoxResizable: true,

  // Toggle drag mode between "crop" and "move" when click twice on the cropper
  toggleDragModeOnDblclick: true,

  // Size limitation
  minCanvasWidth: 0,
  minCanvasHeight: 0,
  minCropBoxWidth: 0,
  minCropBoxHeight: 0,
  minContainerWidth: 200,
  minContainerHeight: 100,

  // Shortcuts of events
  ready: null,
  cropstart: null,
  cropmove: null,
  cropend: null,
  crop: null,
  zoom: null
};

var TEMPLATE = '<div class="cropper-container">' + '<div class="cropper-wrap-box">' + '<div class="cropper-canvas"></div>' + '</div>' + '<div class="cropper-drag-box"></div>' + '<div class="cropper-crop-box">' + '<span class="cropper-view-box"></span>' + '<span class="cropper-dashed dashed-h"></span>' + '<span class="cropper-dashed dashed-v"></span>' + '<span class="cropper-center"></span>' + '<span class="cropper-face"></span>' + '<span class="cropper-line line-e" data-action="e"></span>' + '<span class="cropper-line line-n" data-action="n"></span>' + '<span class="cropper-line line-w" data-action="w"></span>' + '<span class="cropper-line line-s" data-action="s"></span>' + '<span class="cropper-point point-e" data-action="e"></span>' + '<span class="cropper-point point-n" data-action="n"></span>' + '<span class="cropper-point point-w" data-action="w"></span>' + '<span class="cropper-point point-s" data-action="s"></span>' + '<span class="cropper-point point-ne" data-action="ne"></span>' + '<span class="cropper-point point-nw" data-action="nw"></span>' + '<span class="cropper-point point-sw" data-action="sw"></span>' + '<span class="cropper-point point-se" data-action="se"></span>' + '</div>' + '</div>';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

// RegExps
var REGEXP_DATA_URL_HEAD = /^data:.*,/;
var REGEXP_HYPHENATE = /([a-z\d])([A-Z])/g;
var REGEXP_ORIGINS = /^(https?:)\/\/([^:/?#]+):?(\d*)/i;
var REGEXP_SPACES = /\s+/;
var REGEXP_SUFFIX = /^(width|height|left|top|marginLeft|marginTop)$/;
var REGEXP_TRIM = /^\s+(.*)\s+$/;
var REGEXP_USERAGENT = /(Macintosh|iPhone|iPod|iPad).*AppleWebKit/i;

// Utilities
var navigator = typeof window !== 'undefined' ? window.navigator : null;
var IS_SAFARI_OR_UIWEBVIEW = navigator && REGEXP_USERAGENT.test(navigator.userAgent);
var objectProto = Object.prototype;
var toString = objectProto.toString;
var hasOwnProperty = objectProto.hasOwnProperty;
var fromCharCode = String.fromCharCode;

function typeOf(obj) {
  return toString.call(obj).slice(8, -1).toLowerCase();
}

function isNumber(num) {
  return typeof num === 'number' && !isNaN(num);
}

function isUndefined(obj) {
  return typeof obj === 'undefined';
}

function isObject(obj) {
  return (typeof obj === 'undefined' ? 'undefined' : _typeof(obj)) === 'object' && obj !== null;
}

function isPlainObject(obj) {
  if (!isObject(obj)) {
    return false;
  }

  try {
    var _constructor = obj.constructor;
    var prototype = _constructor.prototype;

    return _constructor && prototype && hasOwnProperty.call(prototype, 'isPrototypeOf');
  } catch (e) {
    return false;
  }
}

function isFunction(fn) {
  return typeOf(fn) === 'function';
}

function isArray(arr) {
  return Array.isArray ? Array.isArray(arr) : typeOf(arr) === 'array';
}



function trim(str) {
  if (typeof str === 'string') {
    str = str.trim ? str.trim() : str.replace(REGEXP_TRIM, '$1');
  }

  return str;
}

function each(obj, callback) {
  if (obj && isFunction(callback)) {
    var i = void 0;

    if (isArray(obj) || isNumber(obj.length) /* array-like */) {
        var length = obj.length;

        for (i = 0; i < length; i += 1) {
          if (callback.call(obj, obj[i], i, obj) === false) {
            break;
          }
        }
      } else if (isObject(obj)) {
      Object.keys(obj).forEach(function (key) {
        callback.call(obj, obj[key], key, obj);
      });
    }
  }

  return obj;
}

function extend(obj) {
  for (var _len = arguments.length, args = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    args[_key - 1] = arguments[_key];
  }

  if (isObject(obj) && args.length > 0) {
    if (Object.assign) {
      return Object.assign.apply(Object, [obj].concat(args));
    }

    args.forEach(function (arg) {
      if (isObject(arg)) {
        Object.keys(arg).forEach(function (key) {
          obj[key] = arg[key];
        });
      }
    });
  }

  return obj;
}

function proxy(fn, context) {
  for (var _len2 = arguments.length, args = Array(_len2 > 2 ? _len2 - 2 : 0), _key2 = 2; _key2 < _len2; _key2++) {
    args[_key2 - 2] = arguments[_key2];
  }

  return function () {
    for (var _len3 = arguments.length, args2 = Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
      args2[_key3] = arguments[_key3];
    }

    return fn.apply(context, args.concat(args2));
  };
}

function setStyle(element, styles) {
  var style = element.style;

  each(styles, function (value, property) {
    if (REGEXP_SUFFIX.test(property) && isNumber(value)) {
      value += 'px';
    }

    style[property] = value;
  });
}

function hasClass(element, value) {
  return element.classList ? element.classList.contains(value) : element.className.indexOf(value) > -1;
}

function addClass(element, value) {
  if (!value) {
    return;
  }

  if (isNumber(element.length)) {
    each(element, function (elem) {
      addClass(elem, value);
    });
    return;
  }

  if (element.classList) {
    element.classList.add(value);
    return;
  }

  var className = trim(element.className);

  if (!className) {
    element.className = value;
  } else if (className.indexOf(value) < 0) {
    element.className = className + ' ' + value;
  }
}

function removeClass(element, value) {
  if (!value) {
    return;
  }

  if (isNumber(element.length)) {
    each(element, function (elem) {
      removeClass(elem, value);
    });
    return;
  }

  if (element.classList) {
    element.classList.remove(value);
    return;
  }

  if (element.className.indexOf(value) >= 0) {
    element.className = element.className.replace(value, '');
  }
}

function toggleClass(element, value, added) {
  if (!value) {
    return;
  }

  if (isNumber(element.length)) {
    each(element, function (elem) {
      toggleClass(elem, value, added);
    });
    return;
  }

  // IE10-11 doesn't support the second parameter of `classList.toggle`
  if (added) {
    addClass(element, value);
  } else {
    removeClass(element, value);
  }
}

function hyphenate(str) {
  return str.replace(REGEXP_HYPHENATE, '$1-$2').toLowerCase();
}

function getData(element, name) {
  if (isObject(element[name])) {
    return element[name];
  } else if (element.dataset) {
    return element.dataset[name];
  }

  return element.getAttribute('data-' + hyphenate(name));
}

function setData(element, name, data) {
  if (isObject(data)) {
    element[name] = data;
  } else if (element.dataset) {
    element.dataset[name] = data;
  } else {
    element.setAttribute('data-' + hyphenate(name), data);
  }
}

function removeData(element, name) {
  if (isObject(element[name])) {
    delete element[name];
  } else if (element.dataset) {
    // #128 Safari not allows to delete dataset property
    try {
      delete element.dataset[name];
    } catch (e) {
      element.dataset[name] = null;
    }
  } else {
    element.removeAttribute('data-' + hyphenate(name));
  }
}

function removeListener(element, type, handler) {
  var types = trim(type).split(REGEXP_SPACES);

  if (types.length > 1) {
    each(types, function (t) {
      removeListener(element, t, handler);
    });
    return;
  }

  if (element.removeEventListener) {
    element.removeEventListener(type, handler, false);
  } else if (element.detachEvent) {
    element.detachEvent('on' + type, handler);
  }
}

function addListener(element, type, _handler, once) {
  var types = trim(type).split(REGEXP_SPACES);
  var originalHandler = _handler;

  if (types.length > 1) {
    each(types, function (t) {
      addListener(element, t, _handler);
    });
    return;
  }

  if (once) {
    _handler = function handler() {
      for (var _len4 = arguments.length, args = Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
        args[_key4] = arguments[_key4];
      }

      removeListener(element, type, _handler);

      return originalHandler.apply(element, args);
    };
  }

  if (element.addEventListener) {
    element.addEventListener(type, _handler, false);
  } else if (element.attachEvent) {
    element.attachEvent('on' + type, _handler);
  }
}

function dispatchEvent(element, type, data) {
  if (element.dispatchEvent) {
    var event = void 0;

    // Event and CustomEvent on IE9-11 are global objects, not constructors
    if (isFunction(Event) && isFunction(CustomEvent)) {
      if (isUndefined(data)) {
        event = new Event(type, {
          bubbles: true,
          cancelable: true
        });
      } else {
        event = new CustomEvent(type, {
          detail: data,
          bubbles: true,
          cancelable: true
        });
      }
    } else if (isUndefined(data)) {
      event = document.createEvent('Event');
      event.initEvent(type, true, true);
    } else {
      event = document.createEvent('CustomEvent');
      event.initCustomEvent(type, true, true, data);
    }

    // IE9+
    return element.dispatchEvent(event);
  } else if (element.fireEvent) {
    // IE6-10 (native events only)
    return element.fireEvent('on' + type);
  }

  return true;
}

function getEvent(event) {
  var e = event || window.event;

  // Fix target property (IE8)
  if (!e.target) {
    e.target = e.srcElement || document;
  }

  if (!isNumber(e.pageX) && isNumber(e.clientX)) {
    var eventDoc = event.target.ownerDocument || document;
    var doc = eventDoc.documentElement;
    var body = eventDoc.body;

    e.pageX = e.clientX + ((doc && doc.scrollLeft || body && body.scrollLeft || 0) - (doc && doc.clientLeft || body && body.clientLeft || 0));
    e.pageY = e.clientY + ((doc && doc.scrollTop || body && body.scrollTop || 0) - (doc && doc.clientTop || body && body.clientTop || 0));
  }

  return e;
}

function getOffset(element) {
  var doc = document.documentElement;
  var box = element.getBoundingClientRect();

  return {
    left: box.left + ((window.scrollX || doc && doc.scrollLeft || 0) - (doc && doc.clientLeft || 0)),
    top: box.top + ((window.scrollY || doc && doc.scrollTop || 0) - (doc && doc.clientTop || 0))
  };
}

function getByTag(element, tagName) {
  return element.getElementsByTagName(tagName);
}

function getByClass(element, className) {
  return element.getElementsByClassName ? element.getElementsByClassName(className) : element.querySelectorAll('.' + className);
}

function createElement(tagName) {
  return document.createElement(tagName);
}

function appendChild(element, elem) {
  element.appendChild(elem);
}

function removeChild(element) {
  if (element.parentNode) {
    element.parentNode.removeChild(element);
  }
}

function empty(element) {
  while (element.firstChild) {
    element.removeChild(element.firstChild);
  }
}

function isCrossOriginURL(url) {
  var parts = url.match(REGEXP_ORIGINS);

  return parts && (parts[1] !== location.protocol || parts[2] !== location.hostname || parts[3] !== location.port);
}

function addTimestamp(url) {
  var timestamp = 'timestamp=' + new Date().getTime();

  return url + (url.indexOf('?') === -1 ? '?' : '&') + timestamp;
}

function getImageSize(image, callback) {
  // Modern browsers (ignore Safari)
  if (image.naturalWidth && !IS_SAFARI_OR_UIWEBVIEW) {
    callback(image.naturalWidth, image.naturalHeight);
    return;
  }

  // IE8: Don't use `new Image()` here
  var newImage = createElement('img');

  newImage.onload = function load() {
    callback(this.width, this.height);
  };

  newImage.src = image.src;
}

function getTransforms(data) {
  var transforms = [];
  var translateX = data.translateX;
  var translateY = data.translateY;
  var rotate = data.rotate;
  var scaleX = data.scaleX;
  var scaleY = data.scaleY;

  if (isNumber(translateX) && translateX !== 0) {
    transforms.push('translateX(' + translateX + 'px)');
  }

  if (isNumber(translateY) && translateY !== 0) {
    transforms.push('translateY(' + translateY + 'px)');
  }

  // Rotate should come first before scale to match orientation transform
  if (isNumber(rotate) && rotate !== 0) {
    transforms.push('rotate(' + rotate + 'deg)');
  }

  if (isNumber(scaleX) && scaleX !== 1) {
    transforms.push('scaleX(' + scaleX + ')');
  }

  if (isNumber(scaleY) && scaleY !== 1) {
    transforms.push('scaleY(' + scaleY + ')');
  }

  var transform = transforms.length ? transforms.join(' ') : 'none';

  return {
    WebkitTransform: transform,
    msTransform: transform,
    transform: transform
  };
}

function getRotatedSizes(data, reversed) {
  var deg = Math.abs(data.degree) % 180;
  var arc = (deg > 90 ? 180 - deg : deg) * Math.PI / 180;
  var sinArc = Math.sin(arc);
  var cosArc = Math.cos(arc);
  var width = data.width;
  var height = data.height;
  var aspectRatio = data.aspectRatio;
  var newWidth = void 0;
  var newHeight = void 0;

  if (!reversed) {
    newWidth = width * cosArc + height * sinArc;
    newHeight = width * sinArc + height * cosArc;
  } else {
    newWidth = width / (cosArc + sinArc / aspectRatio);
    newHeight = newWidth / aspectRatio;
  }

  return {
    width: newWidth,
    height: newHeight
  };
}

function getSourceCanvas(image, data, options) {
  var canvas = createElement('canvas');
  var context = canvas.getContext('2d');
  var dstX = 0;
  var dstY = 0;
  var dstWidth = data.naturalWidth;
  var dstHeight = data.naturalHeight;
  var rotate = data.rotate;
  var scaleX = data.scaleX;
  var scaleY = data.scaleY;
  var scalable = isNumber(scaleX) && isNumber(scaleY) && (scaleX !== 1 || scaleY !== 1);
  var rotatable = isNumber(rotate) && rotate !== 0;
  var advanced = rotatable || scalable;
  var canvasWidth = dstWidth * Math.abs(scaleX || 1);
  var canvasHeight = dstHeight * Math.abs(scaleY || 1);
  var translateX = void 0;
  var translateY = void 0;
  var rotated = void 0;

  if (scalable) {
    translateX = canvasWidth / 2;
    translateY = canvasHeight / 2;
  }

  if (rotatable) {
    rotated = getRotatedSizes({
      width: canvasWidth,
      height: canvasHeight,
      degree: rotate
    });

    canvasWidth = rotated.width;
    canvasHeight = rotated.height;
    translateX = canvasWidth / 2;
    translateY = canvasHeight / 2;
  }

  canvas.width = canvasWidth;
  canvas.height = canvasHeight;

  if (options.fillColor) {
    context.fillStyle = options.fillColor;
    context.fillRect(0, 0, canvasWidth, canvasHeight);
  }

  if (advanced) {
    dstX = -dstWidth / 2;
    dstY = -dstHeight / 2;

    context.save();
    context.translate(translateX, translateY);
  }

  // Rotate should come first before scale as in the "getTransform" function
  if (rotatable) {
    context.rotate(rotate * Math.PI / 180);
  }

  if (scalable) {
    context.scale(scaleX, scaleY);
  }

  context.imageSmoothingEnabled = !!options.imageSmoothingEnabled;

  if (options.imageSmoothingQuality) {
    context.imageSmoothingQuality = options.imageSmoothingQuality;
  }

  context.drawImage(image, Math.floor(dstX), Math.floor(dstY), Math.floor(dstWidth), Math.floor(dstHeight));

  if (advanced) {
    context.restore();
  }

  return canvas;
}

function getStringFromCharCode(dataView, start, length) {
  var str = '';
  var i = start;

  for (length += start; i < length; i += 1) {
    str += fromCharCode(dataView.getUint8(i));
  }

  return str;
}

function getOrientation(arrayBuffer) {
  var dataView = new DataView(arrayBuffer);
  var length = dataView.byteLength;
  var orientation = void 0;
  var exifIDCode = void 0;
  var tiffOffset = void 0;
  var firstIFDOffset = void 0;
  var littleEndian = void 0;
  var endianness = void 0;
  var app1Start = void 0;
  var ifdStart = void 0;
  var offset = void 0;
  var i = void 0;

  // Only handle JPEG image (start by 0xFFD8)
  if (dataView.getUint8(0) === 0xFF && dataView.getUint8(1) === 0xD8) {
    offset = 2;

    while (offset < length) {
      if (dataView.getUint8(offset) === 0xFF && dataView.getUint8(offset + 1) === 0xE1) {
        app1Start = offset;
        break;
      }

      offset += 1;
    }
  }

  if (app1Start) {
    exifIDCode = app1Start + 4;
    tiffOffset = app1Start + 10;

    if (getStringFromCharCode(dataView, exifIDCode, 4) === 'Exif') {
      endianness = dataView.getUint16(tiffOffset);
      littleEndian = endianness === 0x4949;

      if (littleEndian || endianness === 0x4D4D /* bigEndian */) {
          if (dataView.getUint16(tiffOffset + 2, littleEndian) === 0x002A) {
            firstIFDOffset = dataView.getUint32(tiffOffset + 4, littleEndian);

            if (firstIFDOffset >= 0x00000008) {
              ifdStart = tiffOffset + firstIFDOffset;
            }
          }
        }
    }
  }

  if (ifdStart) {
    length = dataView.getUint16(ifdStart, littleEndian);

    for (i = 0; i < length; i += 1) {
      offset = ifdStart + i * 12 + 2;

      if (dataView.getUint16(offset, littleEndian) === 0x0112 /* Orientation */) {
          // 8 is the offset of the current tag's value
          offset += 8;

          // Get the original orientation value
          orientation = dataView.getUint16(offset, littleEndian);

          // Override the orientation with its default value for Safari
          if (IS_SAFARI_OR_UIWEBVIEW) {
            dataView.setUint16(offset, 1, littleEndian);
          }

          break;
        }
    }
  }

  return orientation;
}

function dataURLToArrayBuffer(dataURL) {
  var base64 = dataURL.replace(REGEXP_DATA_URL_HEAD, '');
  var binary = atob(base64);
  var length = binary.length;
  var arrayBuffer = new ArrayBuffer(length);
  var dataView = new Uint8Array(arrayBuffer);
  var i = void 0;

  for (i = 0; i < length; i += 1) {
    dataView[i] = binary.charCodeAt(i);
  }

  return arrayBuffer;
}

// Only available for JPEG image
function arrayBufferToDataURL(arrayBuffer) {
  var dataView = new Uint8Array(arrayBuffer);
  var length = dataView.length;
  var base64 = '';
  var i = void 0;

  for (i = 0; i < length; i += 1) {
    base64 += fromCharCode(dataView[i]);
  }

  return 'data:image/jpeg;base64,' + btoa(base64);
}

var render = {
  render: function render() {
    var self = this;

    self.initContainer();
    self.initCanvas();
    self.initCropBox();

    self.renderCanvas();

    if (self.cropped) {
      self.renderCropBox();
    }
  },
  initContainer: function initContainer() {
    var self = this;
    var options = self.options;
    var element = self.element;
    var container = self.container;
    var cropper = self.cropper;
    var hidden = 'cropper-hidden';

    addClass(cropper, hidden);
    removeClass(element, hidden);

    var containerData = {
      width: Math.max(container.offsetWidth, Number(options.minContainerWidth) || 200),
      height: Math.max(container.offsetHeight, Number(options.minContainerHeight) || 100)
    };

    self.containerData = containerData;

    setStyle(cropper, {
      width: containerData.width,
      height: containerData.height
    });

    addClass(element, hidden);
    removeClass(cropper, hidden);
  },


  // Canvas (image wrapper)
  initCanvas: function initCanvas() {
    var self = this;
    var viewMode = self.options.viewMode;
    var containerData = self.containerData;
    var imageData = self.imageData;
    var rotated = Math.abs(imageData.rotate) % 180 === 90;
    var naturalWidth = rotated ? imageData.naturalHeight : imageData.naturalWidth;
    var naturalHeight = rotated ? imageData.naturalWidth : imageData.naturalHeight;
    var aspectRatio = naturalWidth / naturalHeight;
    var canvasWidth = containerData.width;
    var canvasHeight = containerData.height;

    if (containerData.height * aspectRatio > containerData.width) {
      if (viewMode === 3) {
        canvasWidth = containerData.height * aspectRatio;
      } else {
        canvasHeight = containerData.width / aspectRatio;
      }
    } else if (viewMode === 3) {
      canvasHeight = containerData.width / aspectRatio;
    } else {
      canvasWidth = containerData.height * aspectRatio;
    }

    var canvasData = {
      naturalWidth: naturalWidth,
      naturalHeight: naturalHeight,
      aspectRatio: aspectRatio,
      width: canvasWidth,
      height: canvasHeight
    };

    canvasData.left = (containerData.width - canvasWidth) / 2;
    canvasData.top = (containerData.height - canvasHeight) / 2;
    canvasData.oldLeft = canvasData.left;
    canvasData.oldTop = canvasData.top;

    self.canvasData = canvasData;
    self.limited = viewMode === 1 || viewMode === 2;
    self.limitCanvas(true, true);
    self.initialImageData = extend({}, imageData);
    self.initialCanvasData = extend({}, canvasData);
  },
  limitCanvas: function limitCanvas(sizeLimited, positionLimited) {
    var self = this;
    var options = self.options;
    var viewMode = options.viewMode;
    var containerData = self.containerData;
    var canvasData = self.canvasData;
    var aspectRatio = canvasData.aspectRatio;
    var cropBoxData = self.cropBoxData;
    var cropped = self.cropped && cropBoxData;

    if (sizeLimited) {
      var minCanvasWidth = Number(options.minCanvasWidth) || 0;
      var minCanvasHeight = Number(options.minCanvasHeight) || 0;

      if (viewMode > 1) {
        minCanvasWidth = Math.max(minCanvasWidth, containerData.width);
        minCanvasHeight = Math.max(minCanvasHeight, containerData.height);

        if (viewMode === 3) {
          if (minCanvasHeight * aspectRatio > minCanvasWidth) {
            minCanvasWidth = minCanvasHeight * aspectRatio;
          } else {
            minCanvasHeight = minCanvasWidth / aspectRatio;
          }
        }
      } else if (viewMode > 0) {
        if (minCanvasWidth) {
          minCanvasWidth = Math.max(minCanvasWidth, cropped ? cropBoxData.width : 0);
        } else if (minCanvasHeight) {
          minCanvasHeight = Math.max(minCanvasHeight, cropped ? cropBoxData.height : 0);
        } else if (cropped) {
          minCanvasWidth = cropBoxData.width;
          minCanvasHeight = cropBoxData.height;

          if (minCanvasHeight * aspectRatio > minCanvasWidth) {
            minCanvasWidth = minCanvasHeight * aspectRatio;
          } else {
            minCanvasHeight = minCanvasWidth / aspectRatio;
          }
        }
      }

      if (minCanvasWidth && minCanvasHeight) {
        if (minCanvasHeight * aspectRatio > minCanvasWidth) {
          minCanvasHeight = minCanvasWidth / aspectRatio;
        } else {
          minCanvasWidth = minCanvasHeight * aspectRatio;
        }
      } else if (minCanvasWidth) {
        minCanvasHeight = minCanvasWidth / aspectRatio;
      } else if (minCanvasHeight) {
        minCanvasWidth = minCanvasHeight * aspectRatio;
      }

      canvasData.minWidth = minCanvasWidth;
      canvasData.minHeight = minCanvasHeight;
      canvasData.maxWidth = Infinity;
      canvasData.maxHeight = Infinity;
    }

    if (positionLimited) {
      if (viewMode) {
        var newCanvasLeft = containerData.width - canvasData.width;
        var newCanvasTop = containerData.height - canvasData.height;

        canvasData.minLeft = Math.min(0, newCanvasLeft);
        canvasData.minTop = Math.min(0, newCanvasTop);
        canvasData.maxLeft = Math.max(0, newCanvasLeft);
        canvasData.maxTop = Math.max(0, newCanvasTop);

        if (cropped && self.limited) {
          canvasData.minLeft = Math.min(cropBoxData.left, cropBoxData.left + (cropBoxData.width - canvasData.width));
          canvasData.minTop = Math.min(cropBoxData.top, cropBoxData.top + (cropBoxData.height - canvasData.height));
          canvasData.maxLeft = cropBoxData.left;
          canvasData.maxTop = cropBoxData.top;

          if (viewMode === 2) {
            if (canvasData.width >= containerData.width) {
              canvasData.minLeft = Math.min(0, newCanvasLeft);
              canvasData.maxLeft = Math.max(0, newCanvasLeft);
            }

            if (canvasData.height >= containerData.height) {
              canvasData.minTop = Math.min(0, newCanvasTop);
              canvasData.maxTop = Math.max(0, newCanvasTop);
            }
          }
        }
      } else {
        canvasData.minLeft = -canvasData.width;
        canvasData.minTop = -canvasData.height;
        canvasData.maxLeft = containerData.width;
        canvasData.maxTop = containerData.height;
      }
    }
  },
  renderCanvas: function renderCanvas(changed) {
    var self = this;
    var canvasData = self.canvasData;
    var imageData = self.imageData;
    var rotate = imageData.rotate;

    if (self.rotated) {
      self.rotated = false;

      // Computes rotated sizes with image sizes
      var rotatedData = getRotatedSizes({
        width: imageData.width,
        height: imageData.height,
        degree: rotate
      });
      var aspectRatio = rotatedData.width / rotatedData.height;
      var isSquareImage = imageData.aspectRatio === 1;

      if (isSquareImage || aspectRatio !== canvasData.aspectRatio) {
        canvasData.left -= (rotatedData.width - canvasData.width) / 2;
        canvasData.top -= (rotatedData.height - canvasData.height) / 2;
        canvasData.width = rotatedData.width;
        canvasData.height = rotatedData.height;
        canvasData.aspectRatio = aspectRatio;
        canvasData.naturalWidth = imageData.naturalWidth;
        canvasData.naturalHeight = imageData.naturalHeight;

        // Computes rotated sizes with natural image sizes
        if (isSquareImage && rotate % 90 || rotate % 180) {
          var rotatedData2 = getRotatedSizes({
            width: imageData.naturalWidth,
            height: imageData.naturalHeight,
            degree: rotate
          });

          canvasData.naturalWidth = rotatedData2.width;
          canvasData.naturalHeight = rotatedData2.height;
        }

        self.limitCanvas(true, false);
      }
    }

    if (canvasData.width > canvasData.maxWidth || canvasData.width < canvasData.minWidth) {
      canvasData.left = canvasData.oldLeft;
    }

    if (canvasData.height > canvasData.maxHeight || canvasData.height < canvasData.minHeight) {
      canvasData.top = canvasData.oldTop;
    }

    canvasData.width = Math.min(Math.max(canvasData.width, canvasData.minWidth), canvasData.maxWidth);
    canvasData.height = Math.min(Math.max(canvasData.height, canvasData.minHeight), canvasData.maxHeight);

    self.limitCanvas(false, true);

    canvasData.left = Math.min(Math.max(canvasData.left, canvasData.minLeft), canvasData.maxLeft);
    canvasData.top = Math.min(Math.max(canvasData.top, canvasData.minTop), canvasData.maxTop);
    canvasData.oldLeft = canvasData.left;
    canvasData.oldTop = canvasData.top;

    setStyle(self.canvas, extend({
      width: canvasData.width,
      height: canvasData.height
    }, getTransforms({
      translateX: canvasData.left,
      translateY: canvasData.top
    })));

    self.renderImage();

    if (self.cropped && self.limited) {
      self.limitCropBox(true, true);
    }

    if (changed) {
      self.output();
    }
  },
  renderImage: function renderImage(changed) {
    var self = this;
    var canvasData = self.canvasData;
    var imageData = self.imageData;
    var newImageData = void 0;
    var reversedData = void 0;
    var reversedWidth = void 0;
    var reversedHeight = void 0;

    if (imageData.rotate) {
      reversedData = getRotatedSizes({
        width: canvasData.width,
        height: canvasData.height,
        degree: imageData.rotate,
        aspectRatio: imageData.aspectRatio
      }, true);

      reversedWidth = reversedData.width;
      reversedHeight = reversedData.height;

      newImageData = {
        width: reversedWidth,
        height: reversedHeight,
        left: (canvasData.width - reversedWidth) / 2,
        top: (canvasData.height - reversedHeight) / 2
      };
    }

    extend(imageData, newImageData || {
      width: canvasData.width,
      height: canvasData.height,
      left: 0,
      top: 0
    });

    setStyle(self.image, extend({
      width: imageData.width,
      height: imageData.height
    }, getTransforms(extend({
      translateX: imageData.left,
      translateY: imageData.top
    }, imageData))));

    if (changed) {
      self.output();
    }
  },
  initCropBox: function initCropBox() {
    var self = this;
    var options = self.options;
    var aspectRatio = options.aspectRatio;
    var autoCropArea = Number(options.autoCropArea) || 0.8;
    var canvasData = self.canvasData;
    var cropBoxData = {
      width: canvasData.width,
      height: canvasData.height
    };

    if (aspectRatio) {
      if (canvasData.height * aspectRatio > canvasData.width) {
        cropBoxData.height = cropBoxData.width / aspectRatio;
      } else {
        cropBoxData.width = cropBoxData.height * aspectRatio;
      }
    }

    self.cropBoxData = cropBoxData;
    self.limitCropBox(true, true);

    // Initialize auto crop area
    cropBoxData.width = Math.min(Math.max(cropBoxData.width, cropBoxData.minWidth), cropBoxData.maxWidth);
    cropBoxData.height = Math.min(Math.max(cropBoxData.height, cropBoxData.minHeight), cropBoxData.maxHeight);

    // The width/height of auto crop area must large than "minWidth/Height"
    cropBoxData.width = Math.max(cropBoxData.minWidth, cropBoxData.width * autoCropArea);
    cropBoxData.height = Math.max(cropBoxData.minHeight, cropBoxData.height * autoCropArea);
    cropBoxData.left = canvasData.left + (canvasData.width - cropBoxData.width) / 2;
    cropBoxData.top = canvasData.top + (canvasData.height - cropBoxData.height) / 2;
    cropBoxData.oldLeft = cropBoxData.left;
    cropBoxData.oldTop = cropBoxData.top;

    self.initialCropBoxData = extend({}, cropBoxData);
  },
  limitCropBox: function limitCropBox(sizeLimited, positionLimited) {
    var self = this;
    var options = self.options;
    var aspectRatio = options.aspectRatio;
    var containerData = self.containerData;
    var canvasData = self.canvasData;
    var cropBoxData = self.cropBoxData;
    var limited = self.limited;

    if (sizeLimited) {
      var minCropBoxWidth = Number(options.minCropBoxWidth) || 0;
      var minCropBoxHeight = Number(options.minCropBoxHeight) || 0;
      var maxCropBoxWidth = Math.min(containerData.width, limited ? canvasData.width : containerData.width);
      var maxCropBoxHeight = Math.min(containerData.height, limited ? canvasData.height : containerData.height);

      // The min/maxCropBoxWidth/Height must be less than containerWidth/Height
      minCropBoxWidth = Math.min(minCropBoxWidth, containerData.width);
      minCropBoxHeight = Math.min(minCropBoxHeight, containerData.height);

      if (aspectRatio) {
        if (minCropBoxWidth && minCropBoxHeight) {
          if (minCropBoxHeight * aspectRatio > minCropBoxWidth) {
            minCropBoxHeight = minCropBoxWidth / aspectRatio;
          } else {
            minCropBoxWidth = minCropBoxHeight * aspectRatio;
          }
        } else if (minCropBoxWidth) {
          minCropBoxHeight = minCropBoxWidth / aspectRatio;
        } else if (minCropBoxHeight) {
          minCropBoxWidth = minCropBoxHeight * aspectRatio;
        }

        if (maxCropBoxHeight * aspectRatio > maxCropBoxWidth) {
          maxCropBoxHeight = maxCropBoxWidth / aspectRatio;
        } else {
          maxCropBoxWidth = maxCropBoxHeight * aspectRatio;
        }
      }

      // The minWidth/Height must be less than maxWidth/Height
      cropBoxData.minWidth = Math.min(minCropBoxWidth, maxCropBoxWidth);
      cropBoxData.minHeight = Math.min(minCropBoxHeight, maxCropBoxHeight);
      cropBoxData.maxWidth = maxCropBoxWidth;
      cropBoxData.maxHeight = maxCropBoxHeight;
    }

    if (positionLimited) {
      if (limited) {
        cropBoxData.minLeft = Math.max(0, canvasData.left);
        cropBoxData.minTop = Math.max(0, canvasData.top);
        cropBoxData.maxLeft = Math.min(containerData.width, canvasData.left + canvasData.width) - cropBoxData.width;
        cropBoxData.maxTop = Math.min(containerData.height, canvasData.top + canvasData.height) - cropBoxData.height;
      } else {
        cropBoxData.minLeft = 0;
        cropBoxData.minTop = 0;
        cropBoxData.maxLeft = containerData.width - cropBoxData.width;
        cropBoxData.maxTop = containerData.height - cropBoxData.height;
      }
    }
  },
  renderCropBox: function renderCropBox() {
    var self = this;
    var options = self.options;
    var containerData = self.containerData;
    var cropBoxData = self.cropBoxData;

    if (cropBoxData.width > cropBoxData.maxWidth || cropBoxData.width < cropBoxData.minWidth) {
      cropBoxData.left = cropBoxData.oldLeft;
    }

    if (cropBoxData.height > cropBoxData.maxHeight || cropBoxData.height < cropBoxData.minHeight) {
      cropBoxData.top = cropBoxData.oldTop;
    }

    cropBoxData.width = Math.min(Math.max(cropBoxData.width, cropBoxData.minWidth), cropBoxData.maxWidth);
    cropBoxData.height = Math.min(Math.max(cropBoxData.height, cropBoxData.minHeight), cropBoxData.maxHeight);

    self.limitCropBox(false, true);

    cropBoxData.left = Math.min(Math.max(cropBoxData.left, cropBoxData.minLeft), cropBoxData.maxLeft);
    cropBoxData.top = Math.min(Math.max(cropBoxData.top, cropBoxData.minTop), cropBoxData.maxTop);
    cropBoxData.oldLeft = cropBoxData.left;
    cropBoxData.oldTop = cropBoxData.top;

    if (options.movable && options.cropBoxMovable) {
      // Turn to move the canvas when the crop box is equal to the container
      setData(self.face, 'action', cropBoxData.width === containerData.width && cropBoxData.height === containerData.height ? 'move' : 'all');
    }

    setStyle(self.cropBox, extend({
      width: cropBoxData.width,
      height: cropBoxData.height
    }, getTransforms({
      translateX: cropBoxData.left,
      translateY: cropBoxData.top
    })));

    if (self.cropped && self.limited) {
      self.limitCanvas(true, true);
    }

    if (!self.disabled) {
      self.output();
    }
  },
  output: function output() {
    var self = this;

    self.preview();

    if (self.complete) {
      dispatchEvent(self.element, 'crop', self.getData());
    }
  }
};

var DATA_PREVIEW = 'preview';

var preview = {
  initPreview: function initPreview() {
    var self = this;
    var preview = self.options.preview;
    var image = createElement('img');
    var crossOrigin = self.crossOrigin;
    var url = crossOrigin ? self.crossOriginUrl : self.url;

    if (crossOrigin) {
      image.crossOrigin = crossOrigin;
    }

    image.src = url;
    appendChild(self.viewBox, image);
    self.image2 = image;

    if (!preview) {
      return;
    }

    var previews = preview.querySelector ? [preview] : document.querySelectorAll(preview);

    self.previews = previews;

    each(previews, function (element) {
      var img = createElement('img');

      // Save the original size for recover
      setData(element, DATA_PREVIEW, {
        width: element.offsetWidth,
        height: element.offsetHeight,
        html: element.innerHTML
      });

      if (crossOrigin) {
        img.crossOrigin = crossOrigin;
      }

      img.src = url;

      /**
       * Override img element styles
       * Add `display:block` to avoid margin top issue
       * Add `height:auto` to override `height` attribute on IE8
       * (Occur only when margin-top <= -height)
       */

      img.style.cssText = 'display:block;' + 'width:100%;' + 'height:auto;' + 'min-width:0!important;' + 'min-height:0!important;' + 'max-width:none!important;' + 'max-height:none!important;' + 'image-orientation:0deg!important;"';

      empty(element);
      appendChild(element, img);
    });
  },
  resetPreview: function resetPreview() {
    each(this.previews, function (element) {
      var data = getData(element, DATA_PREVIEW);

      setStyle(element, {
        width: data.width,
        height: data.height
      });

      element.innerHTML = data.html;
      removeData(element, DATA_PREVIEW);
    });
  },
  preview: function preview() {
    var self = this;
    var imageData = self.imageData;
    var canvasData = self.canvasData;
    var cropBoxData = self.cropBoxData;
    var cropBoxWidth = cropBoxData.width;
    var cropBoxHeight = cropBoxData.height;
    var width = imageData.width;
    var height = imageData.height;
    var left = cropBoxData.left - canvasData.left - imageData.left;
    var top = cropBoxData.top - canvasData.top - imageData.top;

    if (!self.cropped || self.disabled) {
      return;
    }

    setStyle(self.image2, extend({
      width: width,
      height: height
    }, getTransforms(extend({
      translateX: -left,
      translateY: -top
    }, imageData))));

    each(self.previews, function (element) {
      var data = getData(element, DATA_PREVIEW);
      var originalWidth = data.width;
      var originalHeight = data.height;
      var newWidth = originalWidth;
      var newHeight = originalHeight;
      var ratio = 1;

      if (cropBoxWidth) {
        ratio = originalWidth / cropBoxWidth;
        newHeight = cropBoxHeight * ratio;
      }

      if (cropBoxHeight && newHeight > originalHeight) {
        ratio = originalHeight / cropBoxHeight;
        newWidth = cropBoxWidth * ratio;
        newHeight = originalHeight;
      }

      setStyle(element, {
        width: newWidth,
        height: newHeight
      });

      setStyle(getByTag(element, 'img')[0], extend({
        width: width * ratio,
        height: height * ratio
      }, getTransforms(extend({
        translateX: -left * ratio,
        translateY: -top * ratio
      }, imageData))));
    });
  }
};

// Globals
var PointerEvent = typeof window !== 'undefined' ? window.PointerEvent : null;

// Events
var EVENT_POINTER_DOWN = PointerEvent ? 'pointerdown' : 'touchstart mousedown';
var EVENT_POINTER_MOVE = PointerEvent ? 'pointermove' : 'touchmove mousemove';
var EVENT_POINTER_UP = PointerEvent ? ' pointerup pointercancel' : 'touchend touchcancel mouseup';
var EVENT_WHEEL = 'wheel mousewheel DOMMouseScroll';
var EVENT_DBLCLICK = 'dblclick';
var EVENT_RESIZE = 'resize';
var EVENT_CROP_START = 'cropstart';
var EVENT_CROP_MOVE = 'cropmove';
var EVENT_CROP_END = 'cropend';
var EVENT_CROP$1 = 'crop';
var EVENT_ZOOM = 'zoom';

var events = {
  bind: function bind() {
    var self = this;
    var options = self.options;
    var element = self.element;
    var cropper = self.cropper;

    if (isFunction(options.cropstart)) {
      addListener(element, EVENT_CROP_START, options.cropstart);
    }

    if (isFunction(options.cropmove)) {
      addListener(element, EVENT_CROP_MOVE, options.cropmove);
    }

    if (isFunction(options.cropend)) {
      addListener(element, EVENT_CROP_END, options.cropend);
    }

    if (isFunction(options.crop)) {
      addListener(element, EVENT_CROP$1, options.crop);
    }

    if (isFunction(options.zoom)) {
      addListener(element, EVENT_ZOOM, options.zoom);
    }

    addListener(cropper, EVENT_POINTER_DOWN, self.onCropStart = proxy(self.cropStart, self));

    if (options.zoomable && options.zoomOnWheel) {
      addListener(cropper, EVENT_WHEEL, self.onWheel = proxy(self.wheel, self));
    }

    if (options.toggleDragModeOnDblclick) {
      addListener(cropper, EVENT_DBLCLICK, self.onDblclick = proxy(self.dblclick, self));
    }

    addListener(document, EVENT_POINTER_MOVE, self.onCropMove = proxy(self.cropMove, self));
    addListener(document, EVENT_POINTER_UP, self.onCropEnd = proxy(self.cropEnd, self));

    if (options.responsive) {
      addListener(window, EVENT_RESIZE, self.onResize = proxy(self.resize, self));
    }
  },
  unbind: function unbind() {
    var self = this;
    var options = self.options;
    var element = self.element;
    var cropper = self.cropper;

    if (isFunction(options.cropstart)) {
      removeListener(element, EVENT_CROP_START, options.cropstart);
    }

    if (isFunction(options.cropmove)) {
      removeListener(element, EVENT_CROP_MOVE, options.cropmove);
    }

    if (isFunction(options.cropend)) {
      removeListener(element, EVENT_CROP_END, options.cropend);
    }

    if (isFunction(options.crop)) {
      removeListener(element, EVENT_CROP$1, options.crop);
    }

    if (isFunction(options.zoom)) {
      removeListener(element, EVENT_ZOOM, options.zoom);
    }

    removeListener(cropper, EVENT_POINTER_DOWN, self.onCropStart);

    if (options.zoomable && options.zoomOnWheel) {
      removeListener(cropper, EVENT_WHEEL, self.onWheel);
    }

    if (options.toggleDragModeOnDblclick) {
      removeListener(cropper, EVENT_DBLCLICK, self.onDblclick);
    }

    removeListener(document, EVENT_POINTER_MOVE, self.onCropMove);
    removeListener(document, EVENT_POINTER_UP, self.onCropEnd);

    if (options.responsive) {
      removeListener(window, EVENT_RESIZE, self.onResize);
    }
  }
};

var REGEXP_ACTIONS = /^(e|w|s|n|se|sw|ne|nw|all|crop|move|zoom)$/;

function getPointer(_ref, endOnly) {
  var pageX = _ref.pageX,
      pageY = _ref.pageY;

  var end = {
    endX: pageX,
    endY: pageY
  };

  if (endOnly) {
    return end;
  }

  return extend({
    startX: pageX,
    startY: pageY
  }, end);
}

var handlers = {
  resize: function resize() {
    var self = this;
    var options = self.options;
    var container = self.container;
    var containerData = self.containerData;
    var minContainerWidth = Number(options.minContainerWidth) || 200;
    var minContainerHeight = Number(options.minContainerHeight) || 100;

    if (self.disabled || containerData.width === minContainerWidth || containerData.height === minContainerHeight) {
      return;
    }

    var ratio = container.offsetWidth / containerData.width;

    // Resize when width changed or height changed
    if (ratio !== 1 || container.offsetHeight !== containerData.height) {
      var canvasData = void 0;
      var cropBoxData = void 0;

      if (options.restore) {
        canvasData = self.getCanvasData();
        cropBoxData = self.getCropBoxData();
      }

      self.render();

      if (options.restore) {
        self.setCanvasData(each(canvasData, function (n, i) {
          canvasData[i] = n * ratio;
        }));
        self.setCropBoxData(each(cropBoxData, function (n, i) {
          cropBoxData[i] = n * ratio;
        }));
      }
    }
  },
  dblclick: function dblclick() {
    var self = this;

    if (self.disabled || self.options.dragMode === 'none') {
      return;
    }

    self.setDragMode(hasClass(self.dragBox, 'cropper-crop') ? 'move' : 'crop');
  },
  wheel: function wheel(event) {
    var self = this;
    var e = getEvent(event);
    var ratio = Number(self.options.wheelZoomRatio) || 0.1;
    var delta = 1;

    if (self.disabled) {
      return;
    }

    e.preventDefault();

    // Limit wheel speed to prevent zoom too fast (#21)
    if (self.wheeling) {
      return;
    }

    self.wheeling = true;

    setTimeout(function () {
      self.wheeling = false;
    }, 50);

    if (e.deltaY) {
      delta = e.deltaY > 0 ? 1 : -1;
    } else if (e.wheelDelta) {
      delta = -e.wheelDelta / 120;
    } else if (e.detail) {
      delta = e.detail > 0 ? 1 : -1;
    }

    self.zoom(-delta * ratio, e);
  },
  cropStart: function cropStart(event) {
    var self = this;

    if (self.disabled) {
      return;
    }

    var options = self.options;
    var pointers = self.pointers;
    var e = getEvent(event);
    var action = void 0;

    if (e.changedTouches) {
      // Handle touch event
      each(e.changedTouches, function (touch) {
        pointers[touch.identifier] = getPointer(touch);
      });
    } else {
      // Handle mouse event and pointer event
      pointers[e.pointerId || 0] = getPointer(e);
    }

    if (Object.keys(pointers).length > 1 && options.zoomable && options.zoomOnTouch) {
      action = 'zoom';
    } else {
      action = getData(e.target, 'action');
    }

    if (!REGEXP_ACTIONS.test(action)) {
      return;
    }

    if (dispatchEvent(self.element, 'cropstart', {
      originalEvent: e,
      action: action
    }) === false) {
      return;
    }

    e.preventDefault();

    self.action = action;
    self.cropping = false;

    if (action === 'crop') {
      self.cropping = true;
      addClass(self.dragBox, 'cropper-modal');
    }
  },
  cropMove: function cropMove(event) {
    var self = this;
    var action = self.action;

    if (self.disabled || !action) {
      return;
    }

    var pointers = self.pointers;
    var e = getEvent(event);

    e.preventDefault();

    if (dispatchEvent(self.element, 'cropmove', {
      originalEvent: e,
      action: action
    }) === false) {
      return;
    }

    if (e.changedTouches) {
      each(e.changedTouches, function (touch) {
        extend(pointers[touch.identifier], getPointer(touch, true));
      });
    } else {
      extend(pointers[e.pointerId || 0], getPointer(e, true));
    }

    self.change(e);
  },
  cropEnd: function cropEnd(event) {
    var self = this;

    if (self.disabled) {
      return;
    }

    var action = self.action;
    var pointers = self.pointers;
    var e = getEvent(event);

    if (e.changedTouches) {
      each(e.changedTouches, function (touch) {
        delete pointers[touch.identifier];
      });
    } else {
      delete pointers[e.pointerId || 0];
    }

    if (!action) {
      return;
    }

    e.preventDefault();

    if (!Object.keys(pointers).length) {
      self.action = '';
    }

    if (self.cropping) {
      self.cropping = false;
      toggleClass(self.dragBox, 'cropper-modal', self.cropped && this.options.modal);
    }

    dispatchEvent(self.element, 'cropend', {
      originalEvent: e,
      action: action
    });
  }
};

// Actions
var ACTION_EAST = 'e';
var ACTION_WEST = 'w';
var ACTION_SOUTH = 's';
var ACTION_NORTH = 'n';
var ACTION_SOUTH_EAST = 'se';
var ACTION_SOUTH_WEST = 'sw';
var ACTION_NORTH_EAST = 'ne';
var ACTION_NORTH_WEST = 'nw';

function getMaxZoomRatio(pointers) {
  var pointers2 = extend({}, pointers);
  var ratios = [];

  each(pointers, function (pointer, pointerId) {
    delete pointers2[pointerId];

    each(pointers2, function (pointer2) {
      var x1 = Math.abs(pointer.startX - pointer2.startX);
      var y1 = Math.abs(pointer.startY - pointer2.startY);
      var x2 = Math.abs(pointer.endX - pointer2.endX);
      var y2 = Math.abs(pointer.endY - pointer2.endY);
      var z1 = Math.sqrt(x1 * x1 + y1 * y1);
      var z2 = Math.sqrt(x2 * x2 + y2 * y2);
      var ratio = (z2 - z1) / z1;

      ratios.push(ratio);
    });
  });

  ratios.sort(function (a, b) {
    return Math.abs(a) < Math.abs(b);
  });

  return ratios[0];
}

var change = {
  change: function change(e) {
    var self = this;
    var options = self.options;
    var containerData = self.containerData;
    var canvasData = self.canvasData;
    var cropBoxData = self.cropBoxData;
    var aspectRatio = options.aspectRatio;
    var action = self.action;
    var width = cropBoxData.width;
    var height = cropBoxData.height;
    var left = cropBoxData.left;
    var top = cropBoxData.top;
    var right = left + width;
    var bottom = top + height;
    var minLeft = 0;
    var minTop = 0;
    var maxWidth = containerData.width;
    var maxHeight = containerData.height;
    var renderable = true;
    var offset = void 0;

    // Locking aspect ratio in "free mode" by holding shift key
    if (!aspectRatio && e.shiftKey) {
      aspectRatio = width && height ? width / height : 1;
    }

    if (self.limited) {
      minLeft = cropBoxData.minLeft;
      minTop = cropBoxData.minTop;
      maxWidth = minLeft + Math.min(containerData.width, canvasData.width, canvasData.left + canvasData.width);
      maxHeight = minTop + Math.min(containerData.height, canvasData.height, canvasData.top + canvasData.height);
    }

    var pointers = self.pointers;
    var pointer = pointers[Object.keys(pointers)[0]];
    var range = {
      x: pointer.endX - pointer.startX,
      y: pointer.endY - pointer.startY
    };

    switch (action) {
      // Move crop box
      case 'all':
        left += range.x;
        top += range.y;
        break;

      // Resize crop box
      case ACTION_EAST:
        if (range.x >= 0 && (right >= maxWidth || aspectRatio && (top <= minTop || bottom >= maxHeight))) {
          renderable = false;
          break;
        }

        if (right + range.x > maxWidth) {
          range.x = maxWidth - right;
        }

        width += range.x;

        if (aspectRatio) {
          height = width / aspectRatio;
          top -= range.x / aspectRatio / 2;
        }

        if (width < 0) {
          action = ACTION_WEST;
          width = 0;
        }

        break;

      case ACTION_NORTH:
        if (range.y <= 0 && (top <= minTop || aspectRatio && (left <= minLeft || right >= maxWidth))) {
          renderable = false;
          break;
        }

        if (top + range.y < minTop) {
          range.y = minTop - top;
        }

        height -= range.y;
        top += range.y;

        if (aspectRatio) {
          width = height * aspectRatio;
          left += range.y * aspectRatio / 2;
        }

        if (height < 0) {
          action = ACTION_SOUTH;
          height = 0;
        }

        break;

      case ACTION_WEST:
        if (range.x <= 0 && (left <= minLeft || aspectRatio && (top <= minTop || bottom >= maxHeight))) {
          renderable = false;
          break;
        }

        if (left + range.x < minLeft) {
          range.x = minLeft - left;
        }

        width -= range.x;
        left += range.x;

        if (aspectRatio) {
          height = width / aspectRatio;
          top += range.x / aspectRatio / 2;
        }

        if (width < 0) {
          action = ACTION_EAST;
          width = 0;
        }

        break;

      case ACTION_SOUTH:
        if (range.y >= 0 && (bottom >= maxHeight || aspectRatio && (left <= minLeft || right >= maxWidth))) {
          renderable = false;
          break;
        }

        if (bottom + range.y > maxHeight) {
          range.y = maxHeight - bottom;
        }

        height += range.y;

        if (aspectRatio) {
          width = height * aspectRatio;
          left -= range.y * aspectRatio / 2;
        }

        if (height < 0) {
          action = ACTION_NORTH;
          height = 0;
        }

        break;

      case ACTION_NORTH_EAST:
        if (aspectRatio) {
          if (range.y <= 0 && (top <= minTop || right >= maxWidth)) {
            renderable = false;
            break;
          }

          height -= range.y;
          top += range.y;
          width = height * aspectRatio;
        } else {
          if (range.x >= 0) {
            if (right < maxWidth) {
              width += range.x;
            } else if (range.y <= 0 && top <= minTop) {
              renderable = false;
            }
          } else {
            width += range.x;
          }

          if (range.y <= 0) {
            if (top > minTop) {
              height -= range.y;
              top += range.y;
            }
          } else {
            height -= range.y;
            top += range.y;
          }
        }

        if (width < 0 && height < 0) {
          action = ACTION_SOUTH_WEST;
          height = 0;
          width = 0;
        } else if (width < 0) {
          action = ACTION_NORTH_WEST;
          width = 0;
        } else if (height < 0) {
          action = ACTION_SOUTH_EAST;
          height = 0;
        }

        break;

      case ACTION_NORTH_WEST:
        if (aspectRatio) {
          if (range.y <= 0 && (top <= minTop || left <= minLeft)) {
            renderable = false;
            break;
          }

          height -= range.y;
          top += range.y;
          width = height * aspectRatio;
          left += range.y * aspectRatio;
        } else {
          if (range.x <= 0) {
            if (left > minLeft) {
              width -= range.x;
              left += range.x;
            } else if (range.y <= 0 && top <= minTop) {
              renderable = false;
            }
          } else {
            width -= range.x;
            left += range.x;
          }

          if (range.y <= 0) {
            if (top > minTop) {
              height -= range.y;
              top += range.y;
            }
          } else {
            height -= range.y;
            top += range.y;
          }
        }

        if (width < 0 && height < 0) {
          action = ACTION_SOUTH_EAST;
          height = 0;
          width = 0;
        } else if (width < 0) {
          action = ACTION_NORTH_EAST;
          width = 0;
        } else if (height < 0) {
          action = ACTION_SOUTH_WEST;
          height = 0;
        }

        break;

      case ACTION_SOUTH_WEST:
        if (aspectRatio) {
          if (range.x <= 0 && (left <= minLeft || bottom >= maxHeight)) {
            renderable = false;
            break;
          }

          width -= range.x;
          left += range.x;
          height = width / aspectRatio;
        } else {
          if (range.x <= 0) {
            if (left > minLeft) {
              width -= range.x;
              left += range.x;
            } else if (range.y >= 0 && bottom >= maxHeight) {
              renderable = false;
            }
          } else {
            width -= range.x;
            left += range.x;
          }

          if (range.y >= 0) {
            if (bottom < maxHeight) {
              height += range.y;
            }
          } else {
            height += range.y;
          }
        }

        if (width < 0 && height < 0) {
          action = ACTION_NORTH_EAST;
          height = 0;
          width = 0;
        } else if (width < 0) {
          action = ACTION_SOUTH_EAST;
          width = 0;
        } else if (height < 0) {
          action = ACTION_NORTH_WEST;
          height = 0;
        }

        break;

      case ACTION_SOUTH_EAST:
        if (aspectRatio) {
          if (range.x >= 0 && (right >= maxWidth || bottom >= maxHeight)) {
            renderable = false;
            break;
          }

          width += range.x;
          height = width / aspectRatio;
        } else {
          if (range.x >= 0) {
            if (right < maxWidth) {
              width += range.x;
            } else if (range.y >= 0 && bottom >= maxHeight) {
              renderable = false;
            }
          } else {
            width += range.x;
          }

          if (range.y >= 0) {
            if (bottom < maxHeight) {
              height += range.y;
            }
          } else {
            height += range.y;
          }
        }

        if (width < 0 && height < 0) {
          action = ACTION_NORTH_WEST;
          height = 0;
          width = 0;
        } else if (width < 0) {
          action = ACTION_SOUTH_WEST;
          width = 0;
        } else if (height < 0) {
          action = ACTION_NORTH_EAST;
          height = 0;
        }

        break;

      // Move canvas
      case 'move':
        self.move(range.x, range.y);
        renderable = false;
        break;

      // Zoom canvas
      case 'zoom':
        self.zoom(getMaxZoomRatio(pointers), e);
        renderable = false;
        break;

      // Create crop box
      case 'crop':
        if (!range.x || !range.y) {
          renderable = false;
          break;
        }

        offset = getOffset(self.cropper);
        left = pointer.startX - offset.left;
        top = pointer.startY - offset.top;
        width = cropBoxData.minWidth;
        height = cropBoxData.minHeight;

        if (range.x > 0) {
          action = range.y > 0 ? ACTION_SOUTH_EAST : ACTION_NORTH_EAST;
        } else if (range.x < 0) {
          left -= width;
          action = range.y > 0 ? ACTION_SOUTH_WEST : ACTION_NORTH_WEST;
        }

        if (range.y < 0) {
          top -= height;
        }

        // Show the crop box if is hidden
        if (!self.cropped) {
          removeClass(self.cropBox, 'cropper-hidden');
          self.cropped = true;

          if (self.limited) {
            self.limitCropBox(true, true);
          }
        }

        break;

      default:
    }

    if (renderable) {
      cropBoxData.width = width;
      cropBoxData.height = height;
      cropBoxData.left = left;
      cropBoxData.top = top;
      self.action = action;

      self.renderCropBox();
    }

    // Override
    each(pointers, function (p) {
      p.startX = p.endX;
      p.startY = p.endY;
    });
  }
};

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function getPointersCenter(pointers) {
  var pageX = 0;
  var pageY = 0;
  var count = 0;

  each(pointers, function (_ref) {
    var startX = _ref.startX,
        startY = _ref.startY;

    pageX += startX;
    pageY += startY;
    count += 1;
  });

  pageX /= count;
  pageY /= count;

  return {
    pageX: pageX,
    pageY: pageY
  };
}

var methods = {
  // Show the crop box manually
  crop: function crop() {
    var self = this;

    if (self.ready && !self.disabled) {
      if (!self.cropped) {
        self.cropped = true;
        self.limitCropBox(true, true);

        if (self.options.modal) {
          addClass(self.dragBox, 'cropper-modal');
        }

        removeClass(self.cropBox, 'cropper-hidden');
      }

      self.setCropBoxData(self.initialCropBoxData);
    }

    return self;
  },


  // Reset the image and crop box to their initial states
  reset: function reset() {
    var self = this;

    if (self.ready && !self.disabled) {
      self.imageData = extend({}, self.initialImageData);
      self.canvasData = extend({}, self.initialCanvasData);
      self.cropBoxData = extend({}, self.initialCropBoxData);

      self.renderCanvas();

      if (self.cropped) {
        self.renderCropBox();
      }
    }

    return self;
  },


  // Clear the crop box
  clear: function clear() {
    var self = this;

    if (self.cropped && !self.disabled) {
      extend(self.cropBoxData, {
        left: 0,
        top: 0,
        width: 0,
        height: 0
      });

      self.cropped = false;
      self.renderCropBox();

      self.limitCanvas(true, true);

      // Render canvas after crop box rendered
      self.renderCanvas();

      removeClass(self.dragBox, 'cropper-modal');
      addClass(self.cropBox, 'cropper-hidden');
    }

    return self;
  },


  /**
   * Replace the image's src and rebuild the cropper
   *
   * @param {String} url
   * @param {Boolean} onlyColorChanged (optional)
   */
  replace: function replace(url, onlyColorChanged) {
    var self = this;

    if (!self.disabled && url) {
      if (self.isImg) {
        self.element.src = url;
      }

      if (onlyColorChanged) {
        self.url = url;
        self.image.src = url;

        if (self.ready) {
          self.image2.src = url;

          each(self.previews, function (element) {
            getByTag(element, 'img')[0].src = url;
          });
        }
      } else {
        if (self.isImg) {
          self.replaced = true;
        }

        // Clear previous data
        self.options.data = null;
        self.load(url);
      }
    }

    return self;
  },


  // Enable (unfreeze) the cropper
  enable: function enable() {
    var self = this;

    if (self.ready) {
      self.disabled = false;
      removeClass(self.cropper, 'cropper-disabled');
    }

    return self;
  },


  // Disable (freeze) the cropper
  disable: function disable() {
    var self = this;

    if (self.ready) {
      self.disabled = true;
      addClass(self.cropper, 'cropper-disabled');
    }

    return self;
  },


  // Destroy the cropper and remove the instance from the image
  destroy: function destroy() {
    var self = this;
    var element = self.element;
    var image = self.image;

    if (self.loaded) {
      if (self.isImg && self.replaced) {
        element.src = self.originalUrl;
      }

      self.unbuild();
      removeClass(element, 'cropper-hidden');
    } else if (self.isImg) {
      removeListener(element, 'load', self.onStart);
    } else if (image) {
      removeChild(image);
    }

    removeData(element, 'cropper');

    return self;
  },


  /**
   * Move the canvas with relative offsets
   *
   * @param {Number} offsetX
   * @param {Number} offsetY (optional)
   */
  move: function move(offsetX, offsetY) {
    var self = this;
    var canvasData = self.canvasData;

    return self.moveTo(isUndefined(offsetX) ? offsetX : canvasData.left + Number(offsetX), isUndefined(offsetY) ? offsetY : canvasData.top + Number(offsetY));
  },


  /**
   * Move the canvas to an absolute point
   *
   * @param {Number} x
   * @param {Number} y (optional)
   */
  moveTo: function moveTo(x, y) {
    var self = this;
    var canvasData = self.canvasData;
    var changed = false;

    // If "y" is not present, its default value is "x"
    if (isUndefined(y)) {
      y = x;
    }

    x = Number(x);
    y = Number(y);

    if (self.ready && !self.disabled && self.options.movable) {
      if (isNumber(x)) {
        canvasData.left = x;
        changed = true;
      }

      if (isNumber(y)) {
        canvasData.top = y;
        changed = true;
      }

      if (changed) {
        self.renderCanvas(true);
      }
    }

    return self;
  },


  /**
   * Zoom the canvas with a relative ratio
   *
   * @param {Number} ratio
   * @param {Event} _originalEvent (private)
   */
  zoom: function zoom(ratio, _originalEvent) {
    var self = this;
    var canvasData = self.canvasData;

    ratio = Number(ratio);

    if (ratio < 0) {
      ratio = 1 / (1 - ratio);
    } else {
      ratio = 1 + ratio;
    }

    return self.zoomTo(canvasData.width * ratio / canvasData.naturalWidth, _originalEvent);
  },


  /**
   * Zoom the canvas to an absolute ratio
   *
   * @param {Number} ratio
   * @param {Event} _originalEvent (private)
   */
  zoomTo: function zoomTo(ratio, _originalEvent) {
    var self = this;
    var options = self.options;
    var canvasData = self.canvasData;
    var width = canvasData.width;
    var height = canvasData.height;
    var naturalWidth = canvasData.naturalWidth;
    var naturalHeight = canvasData.naturalHeight;

    ratio = Number(ratio);

    if (ratio >= 0 && self.ready && !self.disabled && options.zoomable) {
      var newWidth = naturalWidth * ratio;
      var newHeight = naturalHeight * ratio;

      if (dispatchEvent(self.element, 'zoom', {
        originalEvent: _originalEvent,
        oldRatio: width / naturalWidth,
        ratio: newWidth / naturalWidth
      }) === false) {
        return self;
      }

      if (_originalEvent) {
        var pointers = self.pointers;
        var offset = getOffset(self.cropper);
        var center = pointers && Object.keys(pointers).length ? getPointersCenter(pointers) : {
          pageX: _originalEvent.pageX,
          pageY: _originalEvent.pageY
        };

        // Zoom from the triggering point of the event
        canvasData.left -= (newWidth - width) * ((center.pageX - offset.left - canvasData.left) / width);
        canvasData.top -= (newHeight - height) * ((center.pageY - offset.top - canvasData.top) / height);
      } else {
        // Zoom from the center of the canvas
        canvasData.left -= (newWidth - width) / 2;
        canvasData.top -= (newHeight - height) / 2;
      }

      canvasData.width = newWidth;
      canvasData.height = newHeight;
      self.renderCanvas(true);
    }

    return self;
  },


  /**
   * Rotate the canvas with a relative degree
   *
   * @param {Number} degree
   */
  rotate: function rotate(degree) {
    var self = this;

    return self.rotateTo((self.imageData.rotate || 0) + Number(degree));
  },


  /**
   * Rotate the canvas to an absolute degree
   * https://developer.mozilla.org/en-US/docs/Web/CSS/transform-function#rotate()
   *
   * @param {Number} degree
   */
  rotateTo: function rotateTo(degree) {
    var self = this;

    degree = Number(degree);

    if (isNumber(degree) && self.ready && !self.disabled && self.options.rotatable) {
      self.imageData.rotate = degree % 360;
      self.rotated = true;
      self.renderCanvas(true);
    }

    return self;
  },


  /**
   * Scale the image
   * https://developer.mozilla.org/en-US/docs/Web/CSS/transform-function#scale()
   *
   * @param {Number} scaleX
   * @param {Number} scaleY (optional)
   */
  scale: function scale(scaleX, scaleY) {
    var self = this;
    var imageData = self.imageData;
    var changed = false;

    // If "scaleY" is not present, its default value is "scaleX"
    if (isUndefined(scaleY)) {
      scaleY = scaleX;
    }

    scaleX = Number(scaleX);
    scaleY = Number(scaleY);

    if (self.ready && !self.disabled && self.options.scalable) {
      if (isNumber(scaleX)) {
        imageData.scaleX = scaleX;
        changed = true;
      }

      if (isNumber(scaleY)) {
        imageData.scaleY = scaleY;
        changed = true;
      }

      if (changed) {
        self.renderImage(true);
      }
    }

    return self;
  },


  /**
   * Scale the abscissa of the image
   *
   * @param {Number} scaleX
   */
  scaleX: function scaleX(_scaleX) {
    var self = this;
    var scaleY = self.imageData.scaleY;

    return self.scale(_scaleX, isNumber(scaleY) ? scaleY : 1);
  },


  /**
   * Scale the ordinate of the image
   *
   * @param {Number} scaleY
   */
  scaleY: function scaleY(_scaleY) {
    var self = this;
    var scaleX = self.imageData.scaleX;

    return self.scale(isNumber(scaleX) ? scaleX : 1, _scaleY);
  },


  /**
   * Get the cropped area position and size data (base on the original image)
   *
   * @param {Boolean} rounded (optional)
   * @return {Object} data
   */
  getData: function getData$$1(rounded) {
    var self = this;
    var options = self.options;
    var imageData = self.imageData;
    var canvasData = self.canvasData;
    var cropBoxData = self.cropBoxData;
    var ratio = void 0;
    var data = void 0;

    if (self.ready && self.cropped) {
      data = {
        x: cropBoxData.left - canvasData.left,
        y: cropBoxData.top - canvasData.top,
        width: cropBoxData.width,
        height: cropBoxData.height
      };

      ratio = imageData.width / imageData.naturalWidth;

      each(data, function (n, i) {
        n /= ratio;
        data[i] = rounded ? Math.round(n) : n;
      });
    } else {
      data = {
        x: 0,
        y: 0,
        width: 0,
        height: 0
      };
    }

    if (options.rotatable) {
      data.rotate = imageData.rotate || 0;
    }

    if (options.scalable) {
      data.scaleX = imageData.scaleX || 1;
      data.scaleY = imageData.scaleY || 1;
    }

    return data;
  },


  /**
   * Set the cropped area position and size with new data
   *
   * @param {Object} data
   */
  setData: function setData$$1(data) {
    var self = this;
    var options = self.options;
    var imageData = self.imageData;
    var canvasData = self.canvasData;
    var cropBoxData = {};
    var rotated = void 0;
    var scaled = void 0;
    var ratio = void 0;

    if (isFunction(data)) {
      data = data.call(self.element);
    }

    if (self.ready && !self.disabled && isPlainObject(data)) {
      if (options.rotatable) {
        if (isNumber(data.rotate) && data.rotate !== imageData.rotate) {
          imageData.rotate = data.rotate;
          rotated = true;
          self.rotated = rotated;
        }
      }

      if (options.scalable) {
        if (isNumber(data.scaleX) && data.scaleX !== imageData.scaleX) {
          imageData.scaleX = data.scaleX;
          scaled = true;
        }

        if (isNumber(data.scaleY) && data.scaleY !== imageData.scaleY) {
          imageData.scaleY = data.scaleY;
          scaled = true;
        }
      }

      if (rotated) {
        self.renderCanvas();
      } else if (scaled) {
        self.renderImage();
      }

      ratio = imageData.width / imageData.naturalWidth;

      if (isNumber(data.x)) {
        cropBoxData.left = data.x * ratio + canvasData.left;
      }

      if (isNumber(data.y)) {
        cropBoxData.top = data.y * ratio + canvasData.top;
      }

      if (isNumber(data.width)) {
        cropBoxData.width = data.width * ratio;
      }

      if (isNumber(data.height)) {
        cropBoxData.height = data.height * ratio;
      }

      self.setCropBoxData(cropBoxData);
    }

    return self;
  },


  /**
   * Get the container size data
   *
   * @return {Object} data
   */
  getContainerData: function getContainerData() {
    var self = this;

    return self.ready ? self.containerData : {};
  },


  /**
   * Get the image position and size data
   *
   * @return {Object} data
   */
  getImageData: function getImageData() {
    var self = this;

    return self.loaded ? self.imageData : {};
  },


  /**
   * Get the canvas position and size data
   *
   * @return {Object} data
   */
  getCanvasData: function getCanvasData() {
    var self = this;
    var canvasData = self.canvasData;
    var data = {};

    if (self.ready) {
      each(['left', 'top', 'width', 'height', 'naturalWidth', 'naturalHeight'], function (n) {
        data[n] = canvasData[n];
      });
    }

    return data;
  },


  /**
   * Set the canvas position and size with new data
   *
   * @param {Object} data
   */
  setCanvasData: function setCanvasData(data) {
    var self = this;
    var canvasData = self.canvasData;
    var aspectRatio = canvasData.aspectRatio;

    if (isFunction(data)) {
      data = data.call(self.element);
    }

    if (self.ready && !self.disabled && isPlainObject(data)) {
      if (isNumber(data.left)) {
        canvasData.left = data.left;
      }

      if (isNumber(data.top)) {
        canvasData.top = data.top;
      }

      if (isNumber(data.width)) {
        canvasData.width = data.width;
        canvasData.height = data.width / aspectRatio;
      } else if (isNumber(data.height)) {
        canvasData.height = data.height;
        canvasData.width = data.height * aspectRatio;
      }

      self.renderCanvas(true);
    }

    return self;
  },


  /**
   * Get the crop box position and size data
   *
   * @return {Object} data
   */
  getCropBoxData: function getCropBoxData() {
    var self = this;
    var cropBoxData = self.cropBoxData;
    var data = void 0;

    if (self.ready && self.cropped) {
      data = {
        left: cropBoxData.left,
        top: cropBoxData.top,
        width: cropBoxData.width,
        height: cropBoxData.height
      };
    }

    return data || {};
  },


  /**
   * Set the crop box position and size with new data
   *
   * @param {Object} data
   */
  setCropBoxData: function setCropBoxData(data) {
    var self = this;
    var cropBoxData = self.cropBoxData;
    var aspectRatio = self.options.aspectRatio;
    var widthChanged = void 0;
    var heightChanged = void 0;

    if (isFunction(data)) {
      data = data.call(self.element);
    }

    if (self.ready && self.cropped && !self.disabled && isPlainObject(data)) {
      if (isNumber(data.left)) {
        cropBoxData.left = data.left;
      }

      if (isNumber(data.top)) {
        cropBoxData.top = data.top;
      }

      if (isNumber(data.width) && data.width !== cropBoxData.width) {
        widthChanged = true;
        cropBoxData.width = data.width;
      }

      if (isNumber(data.height) && data.height !== cropBoxData.height) {
        heightChanged = true;
        cropBoxData.height = data.height;
      }

      if (aspectRatio) {
        if (widthChanged) {
          cropBoxData.height = cropBoxData.width / aspectRatio;
        } else if (heightChanged) {
          cropBoxData.width = cropBoxData.height * aspectRatio;
        }
      }

      self.renderCropBox();
    }

    return self;
  },


  /**
   * Get a canvas drawn the cropped image
   *
   * @param {Object} options (optional)
   * @return {HTMLCanvasElement} canvas
   */
  getCroppedCanvas: function getCroppedCanvas(options) {
    var self = this;

    if (!self.ready || !window.HTMLCanvasElement) {
      return null;
    }

    if (!isPlainObject(options)) {
      options = {};
    }

    // Return the whole canvas if not cropped
    if (!self.cropped) {
      return getSourceCanvas(self.image, self.imageData, options);
    }

    var data = self.getData();
    var originalWidth = data.width;
    var originalHeight = data.height;
    var aspectRatio = originalWidth / originalHeight;
    var scaledWidth = void 0;
    var scaledHeight = void 0;
    var scaledRatio = void 0;

    if (isPlainObject(options)) {
      scaledWidth = options.width;
      scaledHeight = options.height;

      if (scaledWidth) {
        scaledHeight = scaledWidth / aspectRatio;
        scaledRatio = scaledWidth / originalWidth;
      } else if (scaledHeight) {
        scaledWidth = scaledHeight * aspectRatio;
        scaledRatio = scaledHeight / originalHeight;
      }
    }

    // The canvas element will use `Math.floor` on a float number, so floor first
    var canvasWidth = Math.floor(scaledWidth || originalWidth);
    var canvasHeight = Math.floor(scaledHeight || originalHeight);

    var canvas = createElement('canvas');
    var context = canvas.getContext('2d');

    canvas.width = canvasWidth;
    canvas.height = canvasHeight;

    if (options.fillColor) {
      context.fillStyle = options.fillColor;
      context.fillRect(0, 0, canvasWidth, canvasHeight);
    }

    // https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D.drawImage
    var parameters = function () {
      var source = getSourceCanvas(self.image, self.imageData, options);
      var sourceWidth = source.width;
      var sourceHeight = source.height;
      var canvasData = self.canvasData;
      var params = [source];

      // Source canvas
      var srcX = data.x + canvasData.naturalWidth * (Math.abs(data.scaleX || 1) - 1) / 2;
      var srcY = data.y + canvasData.naturalHeight * (Math.abs(data.scaleY || 1) - 1) / 2;
      var srcWidth = void 0;
      var srcHeight = void 0;

      // Destination canvas
      var dstX = void 0;
      var dstY = void 0;
      var dstWidth = void 0;
      var dstHeight = void 0;

      if (srcX <= -originalWidth || srcX > sourceWidth) {
        srcX = 0;
        srcWidth = 0;
        dstX = 0;
        dstWidth = 0;
      } else if (srcX <= 0) {
        dstX = -srcX;
        srcX = 0;
        srcWidth = Math.min(sourceWidth, originalWidth + srcX);
        dstWidth = srcWidth;
      } else if (srcX <= sourceWidth) {
        dstX = 0;
        srcWidth = Math.min(originalWidth, sourceWidth - srcX);
        dstWidth = srcWidth;
      }

      if (srcWidth <= 0 || srcY <= -originalHeight || srcY > sourceHeight) {
        srcY = 0;
        srcHeight = 0;
        dstY = 0;
        dstHeight = 0;
      } else if (srcY <= 0) {
        dstY = -srcY;
        srcY = 0;
        srcHeight = Math.min(sourceHeight, originalHeight + srcY);
        dstHeight = srcHeight;
      } else if (srcY <= sourceHeight) {
        dstY = 0;
        srcHeight = Math.min(originalHeight, sourceHeight - srcY);
        dstHeight = srcHeight;
      }

      params.push(Math.floor(srcX), Math.floor(srcY), Math.floor(srcWidth), Math.floor(srcHeight));

      // Scale destination sizes
      if (scaledRatio) {
        dstX *= scaledRatio;
        dstY *= scaledRatio;
        dstWidth *= scaledRatio;
        dstHeight *= scaledRatio;
      }

      // Avoid "IndexSizeError" in IE and Firefox
      if (dstWidth > 0 && dstHeight > 0) {
        params.push(Math.floor(dstX), Math.floor(dstY), Math.floor(dstWidth), Math.floor(dstHeight));
      }

      return params;
    }();

    context.imageSmoothingEnabled = !!options.imageSmoothingEnabled;

    if (options.imageSmoothingQuality) {
      context.imageSmoothingQuality = options.imageSmoothingQuality;
    }

    context.drawImage.apply(context, _toConsumableArray(parameters));

    return canvas;
  },


  /**
   * Change the aspect ratio of the crop box
   *
   * @param {Number} aspectRatio
   */
  setAspectRatio: function setAspectRatio(aspectRatio) {
    var self = this;
    var options = self.options;

    if (!self.disabled && !isUndefined(aspectRatio)) {
      // 0 -> NaN
      options.aspectRatio = Math.max(0, aspectRatio) || NaN;

      if (self.ready) {
        self.initCropBox();

        if (self.cropped) {
          self.renderCropBox();
        }
      }
    }

    return self;
  },


  /**
   * Change the drag mode
   *
   * @param {String} mode (optional)
   */
  setDragMode: function setDragMode(mode) {
    var self = this;
    var options = self.options;
    var dragBox = self.dragBox;
    var face = self.face;
    var croppable = void 0;
    var movable = void 0;

    if (self.loaded && !self.disabled) {
      croppable = mode === 'crop';
      movable = options.movable && mode === 'move';
      mode = croppable || movable ? mode : 'none';

      setData(dragBox, 'action', mode);
      toggleClass(dragBox, 'cropper-crop', croppable);
      toggleClass(dragBox, 'cropper-move', movable);

      if (!options.cropBoxMovable) {
        // Sync drag mode to crop box when it is not movable
        setData(face, 'action', mode);
        toggleClass(face, 'cropper-crop', croppable);
        toggleClass(face, 'cropper-move', movable);
      }
    }

    return self;
  }
};

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// Constants
var NAMESPACE = 'cropper';

// Classes
var CLASS_HIDDEN = NAMESPACE + '-hidden';

// Events
var EVENT_ERROR = 'error';
var EVENT_LOAD = 'load';
var EVENT_READY = 'ready';
var EVENT_CROP = 'crop';

// RegExps
var REGEXP_DATA_URL = /^data:/;
var REGEXP_DATA_URL_JPEG = /^data:image\/jpeg;base64,/;

var AnotherCropper = void 0;

var Cropper = function () {
  function Cropper(element, options) {
    _classCallCheck(this, Cropper);

    var self = this;

    self.element = element;
    self.options = extend({}, DEFAULTS, isPlainObject(options) && options);
    self.loaded = false;
    self.ready = false;
    self.complete = false;
    self.rotated = false;
    self.cropped = false;
    self.disabled = false;
    self.replaced = false;
    self.limited = false;
    self.wheeling = false;
    self.isImg = false;
    self.originalUrl = '';
    self.canvasData = null;
    self.cropBoxData = null;
    self.previews = null;
    self.pointers = {};
    self.init();
  }

  _createClass(Cropper, [{
    key: 'init',
    value: function init() {
      var self = this;
      var element = self.element;
      var tagName = element.tagName.toLowerCase();
      var url = void 0;

      if (getData(element, NAMESPACE)) {
        return;
      }

      setData(element, NAMESPACE, self);

      if (tagName === 'img') {
        self.isImg = true;

        // e.g.: "img/picture.jpg"
        url = element.getAttribute('src');
        self.originalUrl = url;

        // Stop when it's a blank image
        if (!url) {
          return;
        }

        // e.g.: "http://example.com/img/picture.jpg"
        url = element.src;
      } else if (tagName === 'canvas' && window.HTMLCanvasElement) {
        url = element.toDataURL();
      }

      self.load(url);
    }
  }, {
    key: 'load',
    value: function load(url) {
      var self = this;
      var options = self.options;
      var element = self.element;

      if (!url) {
        return;
      }

      self.url = url;
      self.imageData = {};

      if (!options.checkOrientation || !window.ArrayBuffer) {
        self.clone();
        return;
      }

      // XMLHttpRequest disallows to open a Data URL in some browsers like IE11 and Safari
      if (REGEXP_DATA_URL.test(url)) {
        if (REGEXP_DATA_URL_JPEG.test(url)) {
          self.read(dataURLToArrayBuffer(url));
        } else {
          self.clone();
        }
        return;
      }

      var xhr = new XMLHttpRequest();

      xhr.onerror = function () {
        self.clone();
      };

      xhr.onload = function () {
        self.read(xhr.response);
      };

      if (options.checkCrossOrigin && isCrossOriginURL(url) && element.crossOrigin) {
        url = addTimestamp(url);
      }

      xhr.open('get', url);
      xhr.responseType = 'arraybuffer';
      xhr.withCredentials = element.crossOrigin === 'use-credentials';
      xhr.send();
    }
  }, {
    key: 'read',
    value: function read(arrayBuffer) {
      var self = this;
      var options = self.options;
      var orientation = getOrientation(arrayBuffer);
      var imageData = self.imageData;
      var rotate = 0;
      var scaleX = 1;
      var scaleY = 1;

      if (orientation > 1) {
        self.url = arrayBufferToDataURL(arrayBuffer);

        switch (orientation) {
          // flip horizontal
          case 2:
            scaleX = -1;
            break;

          // rotate left 180°
          case 3:
            rotate = -180;
            break;

          // flip vertical
          case 4:
            scaleY = -1;
            break;

          // flip vertical + rotate right 90°
          case 5:
            rotate = 90;
            scaleY = -1;
            break;

          // rotate right 90°
          case 6:
            rotate = 90;
            break;

          // flip horizontal + rotate right 90°
          case 7:
            rotate = 90;
            scaleX = -1;
            break;

          // rotate left 90°
          case 8:
            rotate = -90;
            break;

          default:
        }
      }

      if (options.rotatable) {
        imageData.rotate = rotate;
      }

      if (options.scalable) {
        imageData.scaleX = scaleX;
        imageData.scaleY = scaleY;
      }

      self.clone();
    }
  }, {
    key: 'clone',
    value: function clone() {
      var self = this;
      var element = self.element;
      var url = self.url;
      var crossOrigin = void 0;
      var crossOriginUrl = void 0;

      if (self.options.checkCrossOrigin && isCrossOriginURL(url)) {
        crossOrigin = element.crossOrigin;

        if (crossOrigin) {
          crossOriginUrl = url;
        } else {
          crossOrigin = 'anonymous';

          // Bust cache when there is not a "crossOrigin" property
          crossOriginUrl = addTimestamp(url);
        }
      }

      self.crossOrigin = crossOrigin;
      self.crossOriginUrl = crossOriginUrl;

      var image = createElement('img');

      if (crossOrigin) {
        image.crossOrigin = crossOrigin;
      }

      image.src = crossOriginUrl || url;

      var start = proxy(self.start, self);
      var stop = proxy(self.stop, self);

      self.image = image;
      self.onStart = start;
      self.onStop = stop;

      if (self.isImg) {
        if (element.complete) {
          self.start();
        } else {
          addListener(element, EVENT_LOAD, start);
        }
      } else {
        addListener(image, EVENT_LOAD, start);
        addListener(image, EVENT_ERROR, stop);
        addClass(image, 'cropper-hide');
        element.parentNode.insertBefore(image, element.nextSibling);
      }
    }
  }, {
    key: 'start',
    value: function start(event) {
      var self = this;
      var image = self.isImg ? self.element : self.image;

      if (event) {
        removeListener(image, EVENT_LOAD, self.onStart);
        removeListener(image, EVENT_ERROR, self.onStop);
      }

      getImageSize(image, function (naturalWidth, naturalHeight) {
        extend(self.imageData, {
          naturalWidth: naturalWidth,
          naturalHeight: naturalHeight,
          aspectRatio: naturalWidth / naturalHeight
        });

        self.loaded = true;
        self.build();
      });
    }
  }, {
    key: 'stop',
    value: function stop() {
      var self = this;
      var image = self.image;

      removeListener(image, EVENT_LOAD, self.onStart);
      removeListener(image, EVENT_ERROR, self.onStop);

      removeChild(image);
      self.image = null;
    }
  }, {
    key: 'build',
    value: function build() {
      var self = this;
      var options = self.options;
      var element = self.element;
      var image = self.image;

      if (!self.loaded) {
        return;
      }

      // Unbuild first when replace
      if (self.ready) {
        self.unbuild();
      }

      // Create cropper elements
      var container = element.parentNode;
      var template = createElement('div');

      template.innerHTML = TEMPLATE;

      var cropper = getByClass(template, 'cropper-container')[0];
      var canvas = getByClass(cropper, 'cropper-canvas')[0];
      var dragBox = getByClass(cropper, 'cropper-drag-box')[0];
      var cropBox = getByClass(cropper, 'cropper-crop-box')[0];
      var face = getByClass(cropBox, 'cropper-face')[0];

      self.container = container;
      self.cropper = cropper;
      self.canvas = canvas;
      self.dragBox = dragBox;
      self.cropBox = cropBox;
      self.viewBox = getByClass(cropper, 'cropper-view-box')[0];
      self.face = face;

      appendChild(canvas, image);

      // Hide the original image
      addClass(element, CLASS_HIDDEN);

      // Inserts the cropper after to the current image
      container.insertBefore(cropper, element.nextSibling);

      // Show the image if is hidden
      if (!self.isImg) {
        removeClass(image, 'cropper-hide');
      }

      self.initPreview();
      self.bind();

      options.aspectRatio = Math.max(0, options.aspectRatio) || NaN;
      options.viewMode = Math.max(0, Math.min(3, Math.round(options.viewMode))) || 0;

      self.cropped = options.autoCrop;

      if (options.autoCrop) {
        if (options.modal) {
          addClass(dragBox, 'cropper-modal');
        }
      } else {
        addClass(cropBox, CLASS_HIDDEN);
      }

      if (!options.guides) {
        addClass(getByClass(cropBox, 'cropper-dashed'), CLASS_HIDDEN);
      }

      if (!options.center) {
        addClass(getByClass(cropBox, 'cropper-center'), CLASS_HIDDEN);
      }

      if (options.background) {
        addClass(cropper, 'cropper-bg');
      }

      if (!options.highlight) {
        addClass(face, 'cropper-invisible');
      }

      if (options.cropBoxMovable) {
        addClass(face, 'cropper-move');
        setData(face, 'action', 'all');
      }

      if (!options.cropBoxResizable) {
        addClass(getByClass(cropBox, 'cropper-line'), CLASS_HIDDEN);
        addClass(getByClass(cropBox, 'cropper-point'), CLASS_HIDDEN);
      }

      self.setDragMode(options.dragMode);
      self.render();
      self.ready = true;
      self.setData(options.data);

      // Call the "ready" option asynchronously to keep "image.cropper" is defined
      self.completing = setTimeout(function () {
        if (isFunction(options.ready)) {
          addListener(element, EVENT_READY, options.ready, true);
        }

        dispatchEvent(element, EVENT_READY);
        dispatchEvent(element, EVENT_CROP, self.getData());

        self.complete = true;
      }, 0);
    }
  }, {
    key: 'unbuild',
    value: function unbuild() {
      var self = this;

      if (!self.ready) {
        return;
      }

      if (!self.complete) {
        clearTimeout(self.completing);
      }

      self.ready = false;
      self.complete = false;
      self.initialImageData = null;

      // Clear `initialCanvasData` is necessary when replace
      self.initialCanvasData = null;
      self.initialCropBoxData = null;
      self.containerData = null;
      self.canvasData = null;

      // Clear `cropBoxData` is necessary when replace
      self.cropBoxData = null;
      self.unbind();

      self.resetPreview();
      self.previews = null;

      self.viewBox = null;
      self.cropBox = null;
      self.dragBox = null;
      self.canvas = null;
      self.container = null;

      removeChild(self.cropper);
      self.cropper = null;
    }
  }], [{
    key: 'noConflict',
    value: function noConflict() {
      window.Cropper = AnotherCropper;
      return Cropper;
    }
  }, {
    key: 'setDefaults',
    value: function setDefaults(options) {
      extend(DEFAULTS, isPlainObject(options) && options);
    }
  }]);

  return Cropper;
}();

extend(Cropper.prototype, render);
extend(Cropper.prototype, preview);
extend(Cropper.prototype, events);
extend(Cropper.prototype, handlers);
extend(Cropper.prototype, change);
extend(Cropper.prototype, methods);

if (typeof window !== 'undefined') {
  AnotherCropper = window.Cropper;
  window.Cropper = Cropper;
}

return Cropper;

})));

/**!
 * Sortable
 * @author	RubaXa   <trash@rubaxa.org>
 * @license MIT
 */

(function sortableModule(factory) {
	"use strict";

	if (typeof define === "function" && define.amd) {
		define(factory);
	}
	else if (typeof module != "undefined" && typeof module.exports != "undefined") {
		module.exports = factory();
	}
	else {
		/* jshint sub:true */
		window["Sortable"] = factory();
	}
})(function sortableFactory() {
	"use strict";

	if (typeof window === "undefined" || !window.document) {
		return function sortableError() {
			throw new Error("Sortable.js requires a window with a document");
		};
	}

	var dragEl,
		parentEl,
		ghostEl,
		cloneEl,
		rootEl,
		nextEl,
		lastDownEl,

		scrollEl,
		scrollParentEl,
		scrollCustomFn,

		lastEl,
		lastCSS,
		lastParentCSS,

		oldIndex,
		newIndex,

		activeGroup,
		putSortable,

		autoScroll = {},

		tapEvt,
		touchEvt,

		moved,

		/** @const */
		R_SPACE = /\s+/g,
		R_FLOAT = /left|right|inline/,

		expando = 'Sortable' + (new Date).getTime(),

		win = window,
		document = win.document,
		parseInt = win.parseInt,
		setTimeout = win.setTimeout,

		$ = win.jQuery || win.Zepto,
		Polymer = win.Polymer,

		captureMode = false,
		passiveMode = false,

		supportDraggable = ('draggable' in document.createElement('div')),
		supportCssPointerEvents = (function (el) {
			// false when IE11
			if (!!navigator.userAgent.match(/(?:Trident.*rv[ :]?11\.|msie)/i)) {
				return false;
			}
			el = document.createElement('x');
			el.style.cssText = 'pointer-events:auto';
			return el.style.pointerEvents === 'auto';
		})(),

		_silent = false,

		abs = Math.abs,
		min = Math.min,

		savedInputChecked = [],
		touchDragOverListeners = [],

		_autoScroll = _throttle(function (/**Event*/evt, /**Object*/options, /**HTMLElement*/rootEl) {
			// Bug: https://bugzilla.mozilla.org/show_bug.cgi?id=505521
			if (rootEl && options.scroll) {
				var _this = rootEl[expando],
					el,
					rect,
					sens = options.scrollSensitivity,
					speed = options.scrollSpeed,

					x = evt.clientX,
					y = evt.clientY,

					winWidth = window.innerWidth,
					winHeight = window.innerHeight,

					vx,
					vy,

					scrollOffsetX,
					scrollOffsetY
				;

				// Delect scrollEl
				if (scrollParentEl !== rootEl) {
					scrollEl = options.scroll;
					scrollParentEl = rootEl;
					scrollCustomFn = options.scrollFn;

					if (scrollEl === true) {
						scrollEl = rootEl;

						do {
							if ((scrollEl.offsetWidth < scrollEl.scrollWidth) ||
								(scrollEl.offsetHeight < scrollEl.scrollHeight)
							) {
								break;
							}
							/* jshint boss:true */
						} while (scrollEl = scrollEl.parentNode);
					}
				}

				if (scrollEl) {
					el = scrollEl;
					rect = scrollEl.getBoundingClientRect();
					vx = (abs(rect.right - x) <= sens) - (abs(rect.left - x) <= sens);
					vy = (abs(rect.bottom - y) <= sens) - (abs(rect.top - y) <= sens);
				}


				if (!(vx || vy)) {
					vx = (winWidth - x <= sens) - (x <= sens);
					vy = (winHeight - y <= sens) - (y <= sens);

					/* jshint expr:true */
					(vx || vy) && (el = win);
				}


				if (autoScroll.vx !== vx || autoScroll.vy !== vy || autoScroll.el !== el) {
					autoScroll.el = el;
					autoScroll.vx = vx;
					autoScroll.vy = vy;

					clearInterval(autoScroll.pid);

					if (el) {
						autoScroll.pid = setInterval(function () {
							scrollOffsetY = vy ? vy * speed : 0;
							scrollOffsetX = vx ? vx * speed : 0;

							if ('function' === typeof(scrollCustomFn)) {
								return scrollCustomFn.call(_this, scrollOffsetX, scrollOffsetY, evt);
							}

							if (el === win) {
								win.scrollTo(win.pageXOffset + scrollOffsetX, win.pageYOffset + scrollOffsetY);
							} else {
								el.scrollTop += scrollOffsetY;
								el.scrollLeft += scrollOffsetX;
							}
						}, 24);
					}
				}
			}
		}, 30),

		_prepareGroup = function (options) {
			function toFn(value, pull) {
				if (value === void 0 || value === true) {
					value = group.name;
				}

				if (typeof value === 'function') {
					return value;
				} else {
					return function (to, from) {
						var fromGroup = from.options.group.name;

						return pull
							? value
							: value && (value.join
								? value.indexOf(fromGroup) > -1
								: (fromGroup == value)
							);
					};
				}
			}

			var group = {};
			var originalGroup = options.group;

			if (!originalGroup || typeof originalGroup != 'object') {
				originalGroup = {name: originalGroup};
			}

			group.name = originalGroup.name;
			group.checkPull = toFn(originalGroup.pull, true);
			group.checkPut = toFn(originalGroup.put);
			group.revertClone = originalGroup.revertClone;

			options.group = group;
		}
	;

	// Detect support a passive mode
	try {
		window.addEventListener('test', null, Object.defineProperty({}, 'passive', {
			get: function () {
				// `false`, because everything starts to work incorrectly and instead of d'n'd,
				// begins the page has scrolled.
				passiveMode = false;
				captureMode = {
					capture: false,
					passive: passiveMode
				};
			}
		}));
	} catch (err) {}

	/**
	 * @class  Sortable
	 * @param  {HTMLElement}  el
	 * @param  {Object}       [options]
	 */
	function Sortable(el, options) {
		if (!(el && el.nodeType && el.nodeType === 1)) {
			throw 'Sortable: `el` must be HTMLElement, and not ' + {}.toString.call(el);
		}

		this.el = el; // root element
		this.options = options = _extend({}, options);


		// Export instance
		el[expando] = this;

		// Default options
		var defaults = {
			group: Math.random(),
			sort: true,
			disabled: false,
			store: null,
			handle: null,
			scroll: true,
			scrollSensitivity: 30,
			scrollSpeed: 10,
			draggable: /[uo]l/i.test(el.nodeName) ? 'li' : '>*',
			ghostClass: 'sortable-ghost',
			chosenClass: 'sortable-chosen',
			dragClass: 'sortable-drag',
			ignore: 'a, img',
			filter: null,
			preventOnFilter: true,
			animation: 0,
			setData: function (dataTransfer, dragEl) {
				dataTransfer.setData('Text', dragEl.textContent);
			},
			dropBubble: false,
			dragoverBubble: false,
			dataIdAttr: 'data-id',
			delay: 0,
			forceFallback: false,
			fallbackClass: 'sortable-fallback',
			fallbackOnBody: false,
			fallbackTolerance: 0,
			fallbackOffset: {x: 0, y: 0},
			supportPointer: Sortable.supportPointer !== false
		};


		// Set default options
		for (var name in defaults) {
			!(name in options) && (options[name] = defaults[name]);
		}

		_prepareGroup(options);

		// Bind all private methods
		for (var fn in this) {
			if (fn.charAt(0) === '_' && typeof this[fn] === 'function') {
				this[fn] = this[fn].bind(this);
			}
		}

		// Setup drag mode
		this.nativeDraggable = options.forceFallback ? false : supportDraggable;

		// Bind events
		_on(el, 'mousedown', this._onTapStart);
		_on(el, 'touchstart', this._onTapStart);
		options.supportPointer && _on(el, 'pointerdown', this._onTapStart);

		if (this.nativeDraggable) {
			_on(el, 'dragover', this);
			_on(el, 'dragenter', this);
		}

		touchDragOverListeners.push(this._onDragOver);

		// Restore sorting
		options.store && this.sort(options.store.get(this));
	}


	Sortable.prototype = /** @lends Sortable.prototype */ {
		constructor: Sortable,

		_onTapStart: function (/** Event|TouchEvent */evt) {
			var _this = this,
				el = this.el,
				options = this.options,
				preventOnFilter = options.preventOnFilter,
				type = evt.type,
				touch = evt.touches && evt.touches[0],
				target = (touch || evt).target,
				originalTarget = evt.target.shadowRoot && (evt.path && evt.path[0]) || target,
				filter = options.filter,
				startIndex;

			_saveInputCheckedState(el);


			// Don't trigger start event when an element is been dragged, otherwise the evt.oldindex always wrong when set option.group.
			if (dragEl) {
				return;
			}

			if (/mousedown|pointerdown/.test(type) && evt.button !== 0 || options.disabled) {
				return; // only left button or enabled
			}

			// cancel dnd if original target is content editable
			if (originalTarget.isContentEditable) {
				return;
			}

			target = _closest(target, options.draggable, el);

			if (!target) {
				return;
			}

			if (lastDownEl === target) {
				// Ignoring duplicate `down`
				return;
			}

			// Get the index of the dragged element within its parent
			startIndex = _index(target, options.draggable);

			// Check filter
			if (typeof filter === 'function') {
				if (filter.call(this, evt, target, this)) {
					_dispatchEvent(_this, originalTarget, 'filter', target, el, el, startIndex);
					preventOnFilter && evt.preventDefault();
					return; // cancel dnd
				}
			}
			else if (filter) {
				filter = filter.split(',').some(function (criteria) {
					criteria = _closest(originalTarget, criteria.trim(), el);

					if (criteria) {
						_dispatchEvent(_this, criteria, 'filter', target, el, el, startIndex);
						return true;
					}
				});

				if (filter) {
					preventOnFilter && evt.preventDefault();
					return; // cancel dnd
				}
			}

			if (options.handle && !_closest(originalTarget, options.handle, el)) {
				return;
			}

			// Prepare `dragstart`
			this._prepareDragStart(evt, touch, target, startIndex);
		},

		_prepareDragStart: function (/** Event */evt, /** Touch */touch, /** HTMLElement */target, /** Number */startIndex) {
			var _this = this,
				el = _this.el,
				options = _this.options,
				ownerDocument = el.ownerDocument,
				dragStartFn;

			if (target && !dragEl && (target.parentNode === el)) {
				tapEvt = evt;

				rootEl = el;
				dragEl = target;
				parentEl = dragEl.parentNode;
				nextEl = dragEl.nextSibling;
				lastDownEl = target;
				activeGroup = options.group;
				oldIndex = startIndex;

				this._lastX = (touch || evt).clientX;
				this._lastY = (touch || evt).clientY;

				dragEl.style['will-change'] = 'all';

				dragStartFn = function () {
					// Delayed drag has been triggered
					// we can re-enable the events: touchmove/mousemove
					_this._disableDelayedDrag();

					// Make the element draggable
					dragEl.draggable = _this.nativeDraggable;

					// Chosen item
					_toggleClass(dragEl, options.chosenClass, true);

					// Bind the events: dragstart/dragend
					_this._triggerDragStart(evt, touch);

					// Drag start event
					_dispatchEvent(_this, rootEl, 'choose', dragEl, rootEl, rootEl, oldIndex);
				};

				// Disable "draggable"
				options.ignore.split(',').forEach(function (criteria) {
					_find(dragEl, criteria.trim(), _disableDraggable);
				});

				_on(ownerDocument, 'mouseup', _this._onDrop);
				_on(ownerDocument, 'touchend', _this._onDrop);
				_on(ownerDocument, 'touchcancel', _this._onDrop);
				_on(ownerDocument, 'selectstart', _this);
				options.supportPointer && _on(ownerDocument, 'pointercancel', _this._onDrop);

				if (options.delay) {
					// If the user moves the pointer or let go the click or touch
					// before the delay has been reached:
					// disable the delayed drag
					_on(ownerDocument, 'mouseup', _this._disableDelayedDrag);
					_on(ownerDocument, 'touchend', _this._disableDelayedDrag);
					_on(ownerDocument, 'touchcancel', _this._disableDelayedDrag);
					_on(ownerDocument, 'mousemove', _this._disableDelayedDrag);
					_on(ownerDocument, 'touchmove', _this._disableDelayedDrag);
					options.supportPointer && _on(ownerDocument, 'pointermove', _this._disableDelayedDrag);

					_this._dragStartTimer = setTimeout(dragStartFn, options.delay);
				} else {
					dragStartFn();
				}


			}
		},

		_disableDelayedDrag: function () {
			var ownerDocument = this.el.ownerDocument;

			clearTimeout(this._dragStartTimer);
			_off(ownerDocument, 'mouseup', this._disableDelayedDrag);
			_off(ownerDocument, 'touchend', this._disableDelayedDrag);
			_off(ownerDocument, 'touchcancel', this._disableDelayedDrag);
			_off(ownerDocument, 'mousemove', this._disableDelayedDrag);
			_off(ownerDocument, 'touchmove', this._disableDelayedDrag);
			_off(ownerDocument, 'pointermove', this._disableDelayedDrag);
		},

		_triggerDragStart: function (/** Event */evt, /** Touch */touch) {
			touch = touch || (evt.pointerType == 'touch' ? evt : null);

			if (touch) {
				// Touch device support
				tapEvt = {
					target: dragEl,
					clientX: touch.clientX,
					clientY: touch.clientY
				};

				this._onDragStart(tapEvt, 'touch');
			}
			else if (!this.nativeDraggable) {
				this._onDragStart(tapEvt, true);
			}
			else {
				_on(dragEl, 'dragend', this);
				_on(rootEl, 'dragstart', this._onDragStart);
			}

			try {
				if (document.selection) {
					// Timeout neccessary for IE9
					_nextTick(function () {
						document.selection.empty();
					});
				} else {
					window.getSelection().removeAllRanges();
				}
			} catch (err) {
			}
		},

		_dragStarted: function () {
			if (rootEl && dragEl) {
				var options = this.options;

				// Apply effect
				_toggleClass(dragEl, options.ghostClass, true);
				_toggleClass(dragEl, options.dragClass, false);

				Sortable.active = this;

				// Drag start event
				_dispatchEvent(this, rootEl, 'start', dragEl, rootEl, rootEl, oldIndex);
			} else {
				this._nulling();
			}
		},

		_emulateDragOver: function () {
			if (touchEvt) {
				if (this._lastX === touchEvt.clientX && this._lastY === touchEvt.clientY) {
					return;
				}

				this._lastX = touchEvt.clientX;
				this._lastY = touchEvt.clientY;

				if (!supportCssPointerEvents) {
					_css(ghostEl, 'display', 'none');
				}

				var target = document.elementFromPoint(touchEvt.clientX, touchEvt.clientY);
				var parent = target;
				var i = touchDragOverListeners.length;

				if (target && target.shadowRoot) {
					target = target.shadowRoot.elementFromPoint(touchEvt.clientX, touchEvt.clientY);
					parent = target;
				}

				if (parent) {
					do {
						if (parent[expando]) {
							while (i--) {
								touchDragOverListeners[i]({
									clientX: touchEvt.clientX,
									clientY: touchEvt.clientY,
									target: target,
									rootEl: parent
								});
							}

							break;
						}

						target = parent; // store last element
					}
					/* jshint boss:true */
					while (parent = parent.parentNode);
				}

				if (!supportCssPointerEvents) {
					_css(ghostEl, 'display', '');
				}
			}
		},


		_onTouchMove: function (/**TouchEvent*/evt) {
			if (tapEvt) {
				var	options = this.options,
					fallbackTolerance = options.fallbackTolerance,
					fallbackOffset = options.fallbackOffset,
					touch = evt.touches ? evt.touches[0] : evt,
					dx = (touch.clientX - tapEvt.clientX) + fallbackOffset.x,
					dy = (touch.clientY - tapEvt.clientY) + fallbackOffset.y,
					translate3d = evt.touches ? 'translate3d(' + dx + 'px,' + dy + 'px,0)' : 'translate(' + dx + 'px,' + dy + 'px)';

				// only set the status to dragging, when we are actually dragging
				if (!Sortable.active) {
					if (fallbackTolerance &&
						min(abs(touch.clientX - this._lastX), abs(touch.clientY - this._lastY)) < fallbackTolerance
					) {
						return;
					}

					this._dragStarted();
				}

				// as well as creating the ghost element on the document body
				this._appendGhost();

				moved = true;
				touchEvt = touch;

				_css(ghostEl, 'webkitTransform', translate3d);
				_css(ghostEl, 'mozTransform', translate3d);
				_css(ghostEl, 'msTransform', translate3d);
				_css(ghostEl, 'transform', translate3d);

				evt.preventDefault();
			}
		},

		_appendGhost: function () {
			if (!ghostEl) {
				var rect = dragEl.getBoundingClientRect(),
					css = _css(dragEl),
					options = this.options,
					ghostRect;

				ghostEl = dragEl.cloneNode(true);

				_toggleClass(ghostEl, options.ghostClass, false);
				_toggleClass(ghostEl, options.fallbackClass, true);
				_toggleClass(ghostEl, options.dragClass, true);

				_css(ghostEl, 'top', rect.top - parseInt(css.marginTop, 10));
				_css(ghostEl, 'left', rect.left - parseInt(css.marginLeft, 10));
				_css(ghostEl, 'width', rect.width);
				_css(ghostEl, 'height', rect.height);
				_css(ghostEl, 'opacity', '0.8');
				_css(ghostEl, 'position', 'fixed');
				_css(ghostEl, 'zIndex', '100000');
				_css(ghostEl, 'pointerEvents', 'none');

				options.fallbackOnBody && document.body.appendChild(ghostEl) || rootEl.appendChild(ghostEl);

				// Fixing dimensions.
				ghostRect = ghostEl.getBoundingClientRect();
				_css(ghostEl, 'width', rect.width * 2 - ghostRect.width);
				_css(ghostEl, 'height', rect.height * 2 - ghostRect.height);
			}
		},

		_onDragStart: function (/**Event*/evt, /**boolean*/useFallback) {
			var _this = this;
			var dataTransfer = evt.dataTransfer;
			var options = _this.options;

			_this._offUpEvents();

			if (activeGroup.checkPull(_this, _this, dragEl, evt)) {
				cloneEl = _clone(dragEl);

				cloneEl.draggable = false;
				cloneEl.style['will-change'] = '';

				_css(cloneEl, 'display', 'none');
				_toggleClass(cloneEl, _this.options.chosenClass, false);

				// #1143: IFrame support workaround
				_this._cloneId = _nextTick(function () {
					rootEl.insertBefore(cloneEl, dragEl);
					_dispatchEvent(_this, rootEl, 'clone', dragEl);
				});
			}

			_toggleClass(dragEl, options.dragClass, true);

			if (useFallback) {
				if (useFallback === 'touch') {
					// Bind touch events
					_on(document, 'touchmove', _this._onTouchMove);
					_on(document, 'touchend', _this._onDrop);
					_on(document, 'touchcancel', _this._onDrop);

					if (options.supportPointer) {
						_on(document, 'pointermove', _this._onTouchMove);
						_on(document, 'pointerup', _this._onDrop);
					}
				} else {
					// Old brwoser
					_on(document, 'mousemove', _this._onTouchMove);
					_on(document, 'mouseup', _this._onDrop);
				}

				_this._loopId = setInterval(_this._emulateDragOver, 50);
			}
			else {
				if (dataTransfer) {
					dataTransfer.effectAllowed = 'move';
					options.setData && options.setData.call(_this, dataTransfer, dragEl);
				}

				_on(document, 'drop', _this);

				// #1143: Бывает элемент с IFrame внутри блокирует `drop`,
				// поэтому если вызвался `mouseover`, значит надо отменять весь d'n'd.
				// Breaking Chrome 62+
				// _on(document, 'mouseover', _this);

				_this._dragStartId = _nextTick(_this._dragStarted);
			}
		},

		_onDragOver: function (/**Event*/evt) {
			var el = this.el,
				target,
				dragRect,
				targetRect,
				revert,
				options = this.options,
				group = options.group,
				activeSortable = Sortable.active,
				isOwner = (activeGroup === group),
				isMovingBetweenSortable = false,
				canSort = options.sort;

			if (evt.preventDefault !== void 0) {
				evt.preventDefault();
				!options.dragoverBubble && evt.stopPropagation();
			}

			if (dragEl.animated) {
				return;
			}

			moved = true;

			if (activeSortable && !options.disabled &&
				(isOwner
					? canSort || (revert = !rootEl.contains(dragEl)) // Reverting item into the original list
					: (
						putSortable === this ||
						(
							(activeSortable.lastPullMode = activeGroup.checkPull(this, activeSortable, dragEl, evt)) &&
							group.checkPut(this, activeSortable, dragEl, evt)
						)
					)
				) &&
				(evt.rootEl === void 0 || evt.rootEl === this.el) // touch fallback
			) {
				// Smart auto-scrolling
				_autoScroll(evt, options, this.el);

				if (_silent) {
					return;
				}

				target = _closest(evt.target, options.draggable, el);
				dragRect = dragEl.getBoundingClientRect();

				if (putSortable !== this) {
					putSortable = this;
					isMovingBetweenSortable = true;
				}

				if (revert) {
					_cloneHide(activeSortable, true);
					parentEl = rootEl; // actualization

					if (cloneEl || nextEl) {
						rootEl.insertBefore(dragEl, cloneEl || nextEl);
					}
					else if (!canSort) {
						rootEl.appendChild(dragEl);
					}

					return;
				}


				if ((el.children.length === 0) || (el.children[0] === ghostEl) ||
					(el === evt.target) && (_ghostIsLast(el, evt))
				) {
					//assign target only if condition is true
					if (el.children.length !== 0 && el.children[0] !== ghostEl && el === evt.target) {
						target = el.lastElementChild;
					}

					if (target) {
						if (target.animated) {
							return;
						}

						targetRect = target.getBoundingClientRect();
					}

					_cloneHide(activeSortable, isOwner);

					if (_onMove(rootEl, el, dragEl, dragRect, target, targetRect, evt) !== false) {
						if (!dragEl.contains(el)) {
							el.appendChild(dragEl);
							parentEl = el; // actualization
						}

						this._animate(dragRect, dragEl);
						target && this._animate(targetRect, target);
					}
				}
				else if (target && !target.animated && target !== dragEl && (target.parentNode[expando] !== void 0)) {
					if (lastEl !== target) {
						lastEl = target;
						lastCSS = _css(target);
						lastParentCSS = _css(target.parentNode);
					}

					targetRect = target.getBoundingClientRect();

					var width = targetRect.right - targetRect.left,
						height = targetRect.bottom - targetRect.top,
						floating = R_FLOAT.test(lastCSS.cssFloat + lastCSS.display)
							|| (lastParentCSS.display == 'flex' && lastParentCSS['flex-direction'].indexOf('row') === 0),
						isWide = (target.offsetWidth > dragEl.offsetWidth),
						isLong = (target.offsetHeight > dragEl.offsetHeight),
						halfway = (floating ? (evt.clientX - targetRect.left) / width : (evt.clientY - targetRect.top) / height) > 0.5,
						nextSibling = target.nextElementSibling,
						after = false
					;

					if (floating) {
						var elTop = dragEl.offsetTop,
							tgTop = target.offsetTop;

						if (elTop === tgTop) {
							after = (target.previousElementSibling === dragEl) && !isWide || halfway && isWide;
						}
						else if (target.previousElementSibling === dragEl || dragEl.previousElementSibling === target) {
							after = (evt.clientY - targetRect.top) / height > 0.5;
						} else {
							after = tgTop > elTop;
						}
						} else if (!isMovingBetweenSortable) {
						after = (nextSibling !== dragEl) && !isLong || halfway && isLong;
					}

					var moveVector = _onMove(rootEl, el, dragEl, dragRect, target, targetRect, evt, after);

					if (moveVector !== false) {
						if (moveVector === 1 || moveVector === -1) {
							after = (moveVector === 1);
						}

						_silent = true;
						setTimeout(_unsilent, 30);

						_cloneHide(activeSortable, isOwner);

						if (!dragEl.contains(el)) {
							if (after && !nextSibling) {
								el.appendChild(dragEl);
							} else {
								target.parentNode.insertBefore(dragEl, after ? nextSibling : target);
							}
						}

						parentEl = dragEl.parentNode; // actualization

						this._animate(dragRect, dragEl);
						this._animate(targetRect, target);
					}
				}
			}
		},

		_animate: function (prevRect, target) {
			var ms = this.options.animation;

			if (ms) {
				var currentRect = target.getBoundingClientRect();

				if (prevRect.nodeType === 1) {
					prevRect = prevRect.getBoundingClientRect();
				}

				_css(target, 'transition', 'none');
				_css(target, 'transform', 'translate3d('
					+ (prevRect.left - currentRect.left) + 'px,'
					+ (prevRect.top - currentRect.top) + 'px,0)'
				);

				target.offsetWidth; // repaint

				_css(target, 'transition', 'all ' + ms + 'ms');
				_css(target, 'transform', 'translate3d(0,0,0)');

				clearTimeout(target.animated);
				target.animated = setTimeout(function () {
					_css(target, 'transition', '');
					_css(target, 'transform', '');
					target.animated = false;
				}, ms);
			}
		},

		_offUpEvents: function () {
			var ownerDocument = this.el.ownerDocument;

			_off(document, 'touchmove', this._onTouchMove);
			_off(document, 'pointermove', this._onTouchMove);
			_off(ownerDocument, 'mouseup', this._onDrop);
			_off(ownerDocument, 'touchend', this._onDrop);
			_off(ownerDocument, 'pointerup', this._onDrop);
			_off(ownerDocument, 'touchcancel', this._onDrop);
			_off(ownerDocument, 'pointercancel', this._onDrop);
			_off(ownerDocument, 'selectstart', this);
		},

		_onDrop: function (/**Event*/evt) {
			var el = this.el,
				options = this.options;

			clearInterval(this._loopId);
			clearInterval(autoScroll.pid);
			clearTimeout(this._dragStartTimer);

			_cancelNextTick(this._cloneId);
			_cancelNextTick(this._dragStartId);

			// Unbind events
			_off(document, 'mouseover', this);
			_off(document, 'mousemove', this._onTouchMove);

			if (this.nativeDraggable) {
				_off(document, 'drop', this);
				_off(el, 'dragstart', this._onDragStart);
			}

			this._offUpEvents();

			if (evt) {
				if (moved) {
					evt.preventDefault();
					!options.dropBubble && evt.stopPropagation();
				}

				ghostEl && ghostEl.parentNode && ghostEl.parentNode.removeChild(ghostEl);

				if (rootEl === parentEl || Sortable.active.lastPullMode !== 'clone') {
					// Remove clone
					cloneEl && cloneEl.parentNode && cloneEl.parentNode.removeChild(cloneEl);
				}

				if (dragEl) {
					if (this.nativeDraggable) {
						_off(dragEl, 'dragend', this);
					}

					_disableDraggable(dragEl);
					dragEl.style['will-change'] = '';

					// Remove class's
					_toggleClass(dragEl, this.options.ghostClass, false);
					_toggleClass(dragEl, this.options.chosenClass, false);

					// Drag stop event
					_dispatchEvent(this, rootEl, 'unchoose', dragEl, parentEl, rootEl, oldIndex);

					if (rootEl !== parentEl) {
						newIndex = _index(dragEl, options.draggable);

						if (newIndex >= 0) {
							// Add event
							_dispatchEvent(null, parentEl, 'add', dragEl, parentEl, rootEl, oldIndex, newIndex);

							// Remove event
							_dispatchEvent(this, rootEl, 'remove', dragEl, parentEl, rootEl, oldIndex, newIndex);

							// drag from one list and drop into another
							_dispatchEvent(null, parentEl, 'sort', dragEl, parentEl, rootEl, oldIndex, newIndex);
							_dispatchEvent(this, rootEl, 'sort', dragEl, parentEl, rootEl, oldIndex, newIndex);
						}
					}
					else {
						if (dragEl.nextSibling !== nextEl) {
							// Get the index of the dragged element within its parent
							newIndex = _index(dragEl, options.draggable);

							if (newIndex >= 0) {
								// drag & drop within the same list
								_dispatchEvent(this, rootEl, 'update', dragEl, parentEl, rootEl, oldIndex, newIndex);
								_dispatchEvent(this, rootEl, 'sort', dragEl, parentEl, rootEl, oldIndex, newIndex);
							}
						}
					}

					if (Sortable.active) {
						/* jshint eqnull:true */
						if (newIndex == null || newIndex === -1) {
							newIndex = oldIndex;
						}

						_dispatchEvent(this, rootEl, 'end', dragEl, parentEl, rootEl, oldIndex, newIndex);

						// Save sorting
						this.save();
					}
				}

			}

			this._nulling();
		},

		_nulling: function() {
			rootEl =
			dragEl =
			parentEl =
			ghostEl =
			nextEl =
			cloneEl =
			lastDownEl =

			scrollEl =
			scrollParentEl =

			tapEvt =
			touchEvt =

			moved =
			newIndex =

			lastEl =
			lastCSS =

			putSortable =
			activeGroup =
			Sortable.active = null;

			savedInputChecked.forEach(function (el) {
				el.checked = true;
			});
			savedInputChecked.length = 0;
		},

		handleEvent: function (/**Event*/evt) {
			switch (evt.type) {
				case 'drop':
				case 'dragend':
					this._onDrop(evt);
					break;

				case 'dragover':
				case 'dragenter':
					if (dragEl) {
						this._onDragOver(evt);
						_globalDragOver(evt);
					}
					break;

				case 'mouseover':
					this._onDrop(evt);
					break;

				case 'selectstart':
					evt.preventDefault();
					break;
			}
		},


		/**
		 * Serializes the item into an array of string.
		 * @returns {String[]}
		 */
		toArray: function () {
			var order = [],
				el,
				children = this.el.children,
				i = 0,
				n = children.length,
				options = this.options;

			for (; i < n; i++) {
				el = children[i];
				if (_closest(el, options.draggable, this.el)) {
					order.push(el.getAttribute(options.dataIdAttr) || _generateId(el));
				}
			}

			return order;
		},


		/**
		 * Sorts the elements according to the array.
		 * @param  {String[]}  order  order of the items
		 */
		sort: function (order) {
			var items = {}, rootEl = this.el;

			this.toArray().forEach(function (id, i) {
				var el = rootEl.children[i];

				if (_closest(el, this.options.draggable, rootEl)) {
					items[id] = el;
				}
			}, this);

			order.forEach(function (id) {
				if (items[id]) {
					rootEl.removeChild(items[id]);
					rootEl.appendChild(items[id]);
				}
			});
		},


		/**
		 * Save the current sorting
		 */
		save: function () {
			var store = this.options.store;
			store && store.set(this);
		},


		/**
		 * For each element in the set, get the first element that matches the selector by testing the element itself and traversing up through its ancestors in the DOM tree.
		 * @param   {HTMLElement}  el
		 * @param   {String}       [selector]  default: `options.draggable`
		 * @returns {HTMLElement|null}
		 */
		closest: function (el, selector) {
			return _closest(el, selector || this.options.draggable, this.el);
		},


		/**
		 * Set/get option
		 * @param   {string} name
		 * @param   {*}      [value]
		 * @returns {*}
		 */
		option: function (name, value) {
			var options = this.options;

			if (value === void 0) {
				return options[name];
			} else {
				options[name] = value;

				if (name === 'group') {
					_prepareGroup(options);
				}
			}
		},


		/**
		 * Destroy
		 */
		destroy: function () {
			var el = this.el;

			el[expando] = null;

			_off(el, 'mousedown', this._onTapStart);
			_off(el, 'touchstart', this._onTapStart);
			_off(el, 'pointerdown', this._onTapStart);

			if (this.nativeDraggable) {
				_off(el, 'dragover', this);
				_off(el, 'dragenter', this);
			}

			// Remove draggable attributes
			Array.prototype.forEach.call(el.querySelectorAll('[draggable]'), function (el) {
				el.removeAttribute('draggable');
			});

			touchDragOverListeners.splice(touchDragOverListeners.indexOf(this._onDragOver), 1);

			this._onDrop();

			this.el = el = null;
		}
	};


	function _cloneHide(sortable, state) {
		if (sortable.lastPullMode !== 'clone') {
			state = true;
		}

		if (cloneEl && (cloneEl.state !== state)) {
			_css(cloneEl, 'display', state ? 'none' : '');

			if (!state) {
				if (cloneEl.state) {
					if (sortable.options.group.revertClone) {
						rootEl.insertBefore(cloneEl, nextEl);
						sortable._animate(dragEl, cloneEl);
					} else {
						rootEl.insertBefore(cloneEl, dragEl);
					}
				}
			}

			cloneEl.state = state;
		}
	}


	function _closest(/**HTMLElement*/el, /**String*/selector, /**HTMLElement*/ctx) {
		if (el) {
			ctx = ctx || document;

			do {
				if ((selector === '>*' && el.parentNode === ctx) || _matches(el, selector)) {
					return el;
				}
				/* jshint boss:true */
			} while (el = _getParentOrHost(el));
		}

		return null;
	}


	function _getParentOrHost(el) {
		var parent = el.host;

		return (parent && parent.nodeType) ? parent : el.parentNode;
	}


	function _globalDragOver(/**Event*/evt) {
		if (evt.dataTransfer) {
			evt.dataTransfer.dropEffect = 'move';
		}
		evt.preventDefault();
	}


	function _on(el, event, fn) {
		el.addEventListener(event, fn, captureMode);
	}


	function _off(el, event, fn) {
		el.removeEventListener(event, fn, captureMode);
	}


	function _toggleClass(el, name, state) {
		if (el) {
			if (el.classList) {
				el.classList[state ? 'add' : 'remove'](name);
			}
			else {
				var className = (' ' + el.className + ' ').replace(R_SPACE, ' ').replace(' ' + name + ' ', ' ');
				el.className = (className + (state ? ' ' + name : '')).replace(R_SPACE, ' ');
			}
		}
	}


	function _css(el, prop, val) {
		var style = el && el.style;

		if (style) {
			if (val === void 0) {
				if (document.defaultView && document.defaultView.getComputedStyle) {
					val = document.defaultView.getComputedStyle(el, '');
				}
				else if (el.currentStyle) {
					val = el.currentStyle;
				}

				return prop === void 0 ? val : val[prop];
			}
			else {
				if (!(prop in style)) {
					prop = '-webkit-' + prop;
				}

				style[prop] = val + (typeof val === 'string' ? '' : 'px');
			}
		}
	}


	function _find(ctx, tagName, iterator) {
		if (ctx) {
			var list = ctx.getElementsByTagName(tagName), i = 0, n = list.length;

			if (iterator) {
				for (; i < n; i++) {
					iterator(list[i], i);
				}
			}

			return list;
		}

		return [];
	}



	function _dispatchEvent(sortable, rootEl, name, targetEl, toEl, fromEl, startIndex, newIndex) {
		sortable = (sortable || rootEl[expando]);

		var evt = document.createEvent('Event'),
			options = sortable.options,
			onName = 'on' + name.charAt(0).toUpperCase() + name.substr(1);

		evt.initEvent(name, true, true);

		evt.to = toEl || rootEl;
		evt.from = fromEl || rootEl;
		evt.item = targetEl || rootEl;
		evt.clone = cloneEl;

		evt.oldIndex = startIndex;
		evt.newIndex = newIndex;

		rootEl.dispatchEvent(evt);

		if (options[onName]) {
			options[onName].call(sortable, evt);
		}
	}


	function _onMove(fromEl, toEl, dragEl, dragRect, targetEl, targetRect, originalEvt, willInsertAfter) {
		var evt,
			sortable = fromEl[expando],
			onMoveFn = sortable.options.onMove,
			retVal;

		evt = document.createEvent('Event');
		evt.initEvent('move', true, true);

		evt.to = toEl;
		evt.from = fromEl;
		evt.dragged = dragEl;
		evt.draggedRect = dragRect;
		evt.related = targetEl || toEl;
		evt.relatedRect = targetRect || toEl.getBoundingClientRect();
		evt.willInsertAfter = willInsertAfter;

		fromEl.dispatchEvent(evt);

		if (onMoveFn) {
			retVal = onMoveFn.call(sortable, evt, originalEvt);
		}

		return retVal;
	}


	function _disableDraggable(el) {
		el.draggable = false;
	}


	function _unsilent() {
		_silent = false;
	}


	/** @returns {HTMLElement|false} */
	function _ghostIsLast(el, evt) {
		var lastEl = el.lastElementChild,
			rect = lastEl.getBoundingClientRect();

		// 5 — min delta
		// abs — нельзя добавлять, а то глюки при наведении сверху
		return (evt.clientY - (rect.top + rect.height) > 5) ||
			(evt.clientX - (rect.left + rect.width) > 5);
	}


	/**
	 * Generate id
	 * @param   {HTMLElement} el
	 * @returns {String}
	 * @private
	 */
	function _generateId(el) {
		var str = el.tagName + el.className + el.src + el.href + el.textContent,
			i = str.length,
			sum = 0;

		while (i--) {
			sum += str.charCodeAt(i);
		}

		return sum.toString(36);
	}

	/**
	 * Returns the index of an element within its parent for a selected set of
	 * elements
	 * @param  {HTMLElement} el
	 * @param  {selector} selector
	 * @return {number}
	 */
	function _index(el, selector) {
		var index = 0;

		if (!el || !el.parentNode) {
			return -1;
		}

		while (el && (el = el.previousElementSibling)) {
			if ((el.nodeName.toUpperCase() !== 'TEMPLATE') && (selector === '>*' || _matches(el, selector))) {
				index++;
			}
		}

		return index;
	}

	function _matches(/**HTMLElement*/el, /**String*/selector) {
		if (el) {
			selector = selector.split('.');

			var tag = selector.shift().toUpperCase(),
				re = new RegExp('\\s(' + selector.join('|') + ')(?=\\s)', 'g');

			return (
				(tag === '' || el.nodeName.toUpperCase() == tag) &&
				(!selector.length || ((' ' + el.className + ' ').match(re) || []).length == selector.length)
			);
		}

		return false;
	}

	function _throttle(callback, ms) {
		var args, _this;

		return function () {
			if (args === void 0) {
				args = arguments;
				_this = this;

				setTimeout(function () {
					if (args.length === 1) {
						callback.call(_this, args[0]);
					} else {
						callback.apply(_this, args);
					}

					args = void 0;
				}, ms);
			}
		};
	}

	function _extend(dst, src) {
		if (dst && src) {
			for (var key in src) {
				if (src.hasOwnProperty(key)) {
					dst[key] = src[key];
				}
			}
		}

		return dst;
	}

	function _clone(el) {
		if (Polymer && Polymer.dom) {
			return Polymer.dom(el).cloneNode(true);
		}
		else if ($) {
			return $(el).clone(true)[0];
		}
		else {
			return el.cloneNode(true);
		}
	}

	function _saveInputCheckedState(root) {
		var inputs = root.getElementsByTagName('input');
		var idx = inputs.length;

		while (idx--) {
			var el = inputs[idx];
			el.checked && savedInputChecked.push(el);
		}
	}

	function _nextTick(fn) {
		return setTimeout(fn, 0);
	}

	function _cancelNextTick(id) {
		return clearTimeout(id);
	}

	// Fixed #973:
	_on(document, 'touchmove', function (evt) {
		if (Sortable.active) {
			evt.preventDefault();
		}
	});

	// Export utils
	Sortable.utils = {
		on: _on,
		off: _off,
		css: _css,
		find: _find,
		is: function (el, selector) {
			return !!_closest(el, selector, el);
		},
		extend: _extend,
		throttle: _throttle,
		closest: _closest,
		toggleClass: _toggleClass,
		clone: _clone,
		index: _index,
		nextTick: _nextTick,
		cancelNextTick: _cancelNextTick
	};


	/**
	 * Create sortable instance
	 * @param {HTMLElement}  el
	 * @param {Object}      [options]
	 */
	Sortable.create = function (el, options) {
		return new Sortable(el, options);
	};


	// Export
	Sortable.version = '1.7.0';
	return Sortable;
});

/*
 * ES2015 accessible modal window system, using ARIA
 * Website: https://van11y.net/accessible-modal/
 * License MIT: https://github.com/nico3333fr/van11y-accessible-modal-aria/blob/master/LICENSE
 */
'use strict';

(function (doc) {

    'use strict';

    var MODAL_JS_CLASS = 'js-modal';
    var MODAL_ID_PREFIX = 'label_modal_';
    var MODAL_CLASS_SUFFIX = 'modal';
    var MODAL_DATA_BACKGROUND_ATTR = 'data-modal-background-click';
    var MODAL_PREFIX_CLASS_ATTR = 'data-modal-prefix-class';
    var MODAL_TEXT_ATTR = 'data-modal-text';
    var MODAL_CONTENT_ID_ATTR = 'data-modal-content-id';
    var MODAL_TITLE_ATTR = 'data-modal-title';
    var MODAL_CLOSE_TEXT_ATTR = 'data-modal-close-text';
    var MODAL_CLOSE_TITLE_ATTR = 'data-modal-close-title';
    var MODAL_CLOSE_IMG_ATTR = 'data-modal-close-img';
    var MODAL_ROLE = 'dialog';

    var MODAL_BUTTON_CLASS_SUFFIX = 'modal-close';
    var MODAL_BUTTON_JS_ID = 'js-modal-close';
    var MODAL_BUTTON_JS_CLASS = 'js-modal-close';
    var MODAL_BUTTON_CONTENT_BACK_ID = 'data-content-back-id';
    var MODAL_BUTTON_FOCUS_BACK_ID = 'data-focus-back';

    var MODAL_CONTENT_CLASS_SUFFIX = 'modal__content';
    var MODAL_CONTENT_JS_ID = 'js-modal-content';

    var MODAL_CLOSE_IMG_CLASS_SUFFIX = 'modal__closeimg';
    var MODAL_CLOSE_TEXT_CLASS_SUFFIX = 'modal-close__text';

    var MODAL_TITLE_ID = 'modal-title';
    var MODAL_TITLE_CLASS_SUFFIX = 'modal-title';

    var FOCUSABLE_ELEMENTS_STRING = "a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, *[tabindex], *[contenteditable]";
    var WRAPPER_PAGE_JS = 'js-modal-page';

    var MODAL_JS_ID = 'js-modal';

    var MODAL_OVERLAY_ID = 'js-modal-overlay';
    var MODAL_OVERLAY_CLASS_SUFFIX = 'modal-overlay';
    var MODAL_OVERLAY_TXT = 'Close modal';
    var MODAL_OVERLAY_BG_ENABLED_ATTR = 'data-background-click';

    var VISUALLY_HIDDEN_CLASS = 'invisible';
    var NO_SCROLL_CLASS = 'no-scroll';

    var ATTR_ROLE = 'role';
    var ATTR_OPEN = 'open';
    var ATTR_LABELLEDBY = 'aria-labelledby';
    var ATTR_HIDDEN = 'aria-hidden';
    var ATTR_MODAL = 'aria-modal="true"';

    var findById = function findById(id) {
        return doc.getElementById(id);
    };

    var addClass = function addClass(el, className) {
        if (el.classList) {
            el.classList.add(className); // IE 10+
        } else {
                el.className += ' ' + className; // IE 8+
            }
    };

    var removeClass = function removeClass(el, className) {
        if (el.classList) {
            el.classList.remove(className); // IE 10+
        } else {
                el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' '); // IE 8+
            }
    };

    var hasClass = function hasClass(el, className) {
        if (el.classList) {
            return el.classList.contains(className); // IE 10+
        } else {
                return new RegExp('(^| )' + className + '( |$)', 'gi').test(el.className); // IE 8+ ?
            }
    };
    /*const wrapInner = (el, wrapper_el) => { // doesn't work on IE/Edge, f…
        while (el.firstChild)
            wrapper_el.append(el.firstChild);
        el.append(wrapper_el);
      }*/
    function wrapInner(parent, wrapper) {
        if (typeof wrapper === "string") wrapper = document.createElement(wrapper);

        parent.appendChild(wrapper);

        while (parent.firstChild !== wrapper) wrapper.appendChild(parent.firstChild);
    }

    function remove(el) {
        /* node.remove() is too modern for IE≤11 */
        el.parentNode.removeChild(el);
    }

    /* gets an element el, search if it is child of parent class, returns id of the parent */
    var searchParent = function searchParent(el, parentClass) {
        var found = false;
        var parentElement = el.parentNode;
        while (parentElement && found === false) {
            if (hasClass(parentElement, parentClass) === true) {
                found = true;
            } else {
                parentElement = parentElement.parentNode;
            }
        }
        if (found === true) {
            return parentElement.getAttribute('id');
        } else {
            return '';
        }
    };

    /**
     * Create the template for an overlay
     * @param  {Object} config
     * @return {String}
     */
    var createOverlay = function createOverlay(config) {

        var id = MODAL_OVERLAY_ID;
        var overlayText = config.text || MODAL_OVERLAY_TXT;
        var overlayClass = config.prefixClass + MODAL_OVERLAY_CLASS_SUFFIX;
        var overlayBackgroundEnabled = config.backgroundEnabled === 'disabled' ? 'disabled' : 'enabled';

        return '<span \n                  id="' + id + '"\n                  class="' + overlayClass + '"\n                  ' + MODAL_OVERLAY_BG_ENABLED_ATTR + '="' + overlayBackgroundEnabled + '" \n                  title="' + overlayText + '"\n                  >\n                  <span class="' + VISUALLY_HIDDEN_CLASS + '">' + overlayText + '</span>\n                </span>';
    };

    /**
     * Create the template for a modal
     * @param  {Object} config
     * @return {String}
     */
    var createModal = function createModal(config) {

        var id = MODAL_JS_ID;
        var modalClassName = config.modalPrefixClass + MODAL_CLASS_SUFFIX;
        var buttonCloseClassName = config.modalPrefixClass + MODAL_BUTTON_CLASS_SUFFIX;
        var buttonCloseInner = config.modalCloseImgPath ? '<img\n                                        src="' + config.modalCloseImgPath + '" \n                                        alt="' + config.modalCloseText + '" \n                                        class="' + MODAL_CLOSE_IMG_CLASS_SUFFIX + '"\n                                        />' : '<span \n                                        class="' + MODAL_CLOSE_TEXT_CLASS_SUFFIX + '">\n                                        ' + config.modalCloseText + '\n                                       </span>';
        var contentClassName = config.modalPrefixClass + MODAL_CONTENT_CLASS_SUFFIX;
        var titleClassName = config.modalPrefixClass + MODAL_TITLE_CLASS_SUFFIX;
        var title = config.modalTitle !== '' ? '<h1 \n                                        id="' + MODAL_TITLE_ID + '" \n                                        class="' + titleClassName + '">\n                                        ' + config.modalTitle + '\n                                       </h1>' : '';
        var button_close = '<button \n                             type="button"\n                             class="' + MODAL_BUTTON_JS_CLASS + ' ' + buttonCloseClassName + '"\n                             id="' + MODAL_BUTTON_JS_ID + '"\n                             title="' + config.modalCloseTitle + '"\n                             ' + MODAL_BUTTON_CONTENT_BACK_ID + '="' + config.modalContentId + '"\n                             ' + MODAL_BUTTON_FOCUS_BACK_ID + '="' + config.modalFocusBackId + '"\n                             >\n                             ' + buttonCloseInner + '\n                            </button>';
        var content = config.modalText;

        // If there is no content but an id we try to fetch content id
        if (content === '' && config.modalContentId) {
            var contentFromId = findById(config.modalContentId);
            if (contentFromId) {
                content = '<div \n                            id="' + MODAL_CONTENT_JS_ID + '">\n                            ' + contentFromId.innerHTML + '\n                           </div';
                // we remove content from its source to avoid id duplicates, etc.
                contentFromId.innerHTML = '';
            }
        }

        return '<dialog \n                  id="' + id + '"\n                  ' + ATTR_ROLE + '="' + MODAL_ROLE + '"\n                  class="' + modalClassName + '"\n                  ' + ATTR_OPEN + '\n                  ' + ATTR_MODAL + '\n                  ' + ATTR_LABELLEDBY + '="' + MODAL_TITLE_ID + '"\n                  >\n                  <div role="document">\n                    ' + button_close + '\n                    <div class="' + contentClassName + '">\n                      ' + title + '\n                      ' + content + '\n                    </div>\n                  </div>\n                </dialog>';
    };

    var closeModal = function closeModal(config) {

        remove(config.modal);
        remove(config.overlay);

        if (config.contentBackId !== '') {
            var contentBack = findById(config.contentBackId);
            if (contentBack) {
                contentBack.innerHTML = config.modalContent;
            }
        }

        if (config.modalFocusBackId) {
            var contentFocus = findById(config.modalFocusBackId);
            if (contentFocus) {
                contentFocus.focus();
            }
        }
    };

    // Find all modals
    var $listModals = function $listModals() {
        return [].slice.call(doc.querySelectorAll('.' + MODAL_JS_CLASS));
    };

    var onLoad = function onLoad() {

        $listModals().forEach(function (modal_node, index) {

            var iLisible = index + 1;
            var wrapperBody = findById(WRAPPER_PAGE_JS);
            var body = doc.querySelector('body');

            modal_node.setAttribute('id', MODAL_ID_PREFIX + iLisible);

            if (wrapperBody === null || wrapperBody.length === 0) {
                var wrapper = doc.createElement('DIV');
                wrapper.setAttribute('id', WRAPPER_PAGE_JS);
                wrapInner(body, wrapper);
            }
        });

        // click on
        ['click', 'keydown'].forEach(function (eventName) {

            doc.body.addEventListener(eventName, function (e) {

                // click on link modal
                if (hasClass(e.target, MODAL_JS_CLASS) === true && eventName === 'click') {
                    var body = doc.querySelector('body');
                    var modalLauncher = e.target;
                    var modalPrefixClass = modalLauncher.hasAttribute(MODAL_PREFIX_CLASS_ATTR) === true ? modalLauncher.getAttribute(MODAL_PREFIX_CLASS_ATTR) + '-' : '';
                    var modalText = modalLauncher.hasAttribute(MODAL_TEXT_ATTR) === true ? modalLauncher.getAttribute(MODAL_TEXT_ATTR) : '';
                    var modalContentId = modalLauncher.hasAttribute(MODAL_CONTENT_ID_ATTR) === true ? modalLauncher.getAttribute(MODAL_CONTENT_ID_ATTR) : '';
                    var modalTitle = modalLauncher.hasAttribute(MODAL_TITLE_ATTR) === true ? modalLauncher.getAttribute(MODAL_TITLE_ATTR) : '';
                    var modalCloseText = modalLauncher.hasAttribute(MODAL_CLOSE_TEXT_ATTR) === true ? modalLauncher.getAttribute(MODAL_CLOSE_TEXT_ATTR) : MODAL_OVERLAY_TXT;
                    var modalCloseTitle = modalLauncher.hasAttribute(MODAL_CLOSE_TITLE_ATTR) === true ? modalLauncher.getAttribute(MODAL_CLOSE_TITLE_ATTR) : modalCloseText;
                    var modalCloseImgPath = modalLauncher.hasAttribute(MODAL_CLOSE_IMG_ATTR) === true ? modalLauncher.getAttribute(MODAL_CLOSE_IMG_ATTR) : '';
                    var backgroundEnabled = modalLauncher.hasAttribute(MODAL_DATA_BACKGROUND_ATTR) === true ? modalLauncher.getAttribute(MODAL_DATA_BACKGROUND_ATTR) : '';

                    var wrapperBody = findById(WRAPPER_PAGE_JS);

                    // insert overlay
                    body.insertAdjacentHTML('beforeEnd', createOverlay({
                        text: modalCloseTitle,
                        backgroundEnabled: backgroundEnabled,
                        prefixClass: modalPrefixClass
                    }));

                    // insert modal
                    body.insertAdjacentHTML('beforeEnd', createModal({
                        modalText: modalText,
                        modalPrefixClass: modalPrefixClass,
                        backgroundEnabled: modalContentId,
                        modalTitle: modalTitle,
                        modalCloseText: modalCloseText,
                        modalCloseTitle: modalCloseTitle,
                        modalCloseImgPath: modalCloseImgPath,
                        modalContentId: modalContentId,
                        modalFocusBackId: modalLauncher.getAttribute('id')
                    }));

                    // hide page
                    wrapperBody.setAttribute(ATTR_HIDDEN, 'true');

                    // add class noscroll to body
                    addClass(body, NO_SCROLL_CLASS);

                    // give focus to close button
                    var closeButton = findById(MODAL_BUTTON_JS_ID);
                    closeButton.focus();

                    e.preventDefault();
                }

                // click on close button or on overlay not blocked
                var parentButton = searchParent(e.target, MODAL_BUTTON_JS_CLASS);
                if ((e.target.getAttribute('id') === MODAL_BUTTON_JS_ID || parentButton !== '' || e.target.getAttribute('id') === MODAL_OVERLAY_ID) && eventName === 'click') {
                    var body = doc.querySelector('body');
                    var wrapperBody = findById(WRAPPER_PAGE_JS);
                    var modal = findById(MODAL_JS_ID);
                    var modalContent = findById(MODAL_CONTENT_JS_ID) ? findById(MODAL_CONTENT_JS_ID).innerHTML : '';
                    var overlay = findById(MODAL_OVERLAY_ID);
                    var modalButtonClose = findById(MODAL_BUTTON_JS_ID);
                    var modalFocusBackId = modalButtonClose.getAttribute(MODAL_BUTTON_FOCUS_BACK_ID);
                    var contentBackId = modalButtonClose.getAttribute(MODAL_BUTTON_CONTENT_BACK_ID);
                    var backgroundEnabled = overlay.getAttribute(MODAL_OVERLAY_BG_ENABLED_ATTR);

                    if (!(e.target.getAttribute('id') === MODAL_OVERLAY_ID && backgroundEnabled === 'disabled')) {

                        closeModal({
                            modal: modal,
                            modalContent: modalContent,
                            overlay: overlay,
                            modalFocusBackId: modalFocusBackId,
                            contentBackId: contentBackId,
                            backgroundEnabled: backgroundEnabled,
                            fromId: e.target.getAttribute('id')
                        });

                        // show back page
                        wrapperBody.removeAttribute(ATTR_HIDDEN);

                        // remove class noscroll to body
                        removeClass(body, NO_SCROLL_CLASS);
                    }
                }

                // strike a key when modal opened
                if (findById(MODAL_JS_ID) && eventName === 'keydown') {
                    var body = doc.querySelector('body');
                    var wrapperBody = findById(WRAPPER_PAGE_JS);
                    var modal = findById(MODAL_JS_ID);
                    var modalContent = findById(MODAL_CONTENT_JS_ID) ? findById(MODAL_CONTENT_JS_ID).innerHTML : '';
                    var overlay = findById(MODAL_OVERLAY_ID);
                    var modalButtonClose = findById(MODAL_BUTTON_JS_ID);
                    var modalFocusBackId = modalButtonClose.getAttribute(MODAL_BUTTON_FOCUS_BACK_ID);
                    var contentBackId = modalButtonClose.getAttribute(MODAL_BUTTON_CONTENT_BACK_ID);
                    var $listFocusables = [].slice.call(modal.querySelectorAll(FOCUSABLE_ELEMENTS_STRING));

                    // esc
                    if (e.keyCode === 27) {

                        closeModal({
                            modal: modal,
                            modalContent: modalContent,
                            overlay: overlay,
                            modalFocusBackId: modalFocusBackId,
                            contentBackId: contentBackId
                        });

                        // show back page
                        wrapperBody.removeAttribute(ATTR_HIDDEN);

                        // remove class noscroll to body
                        removeClass(body, NO_SCROLL_CLASS);
                    }

                    // tab or Maj Tab in modal => capture focus            
                    if (e.keyCode === 9 && $listFocusables.indexOf(e.target) >= 0) {

                        // maj-tab on first element focusable => focus on last
                        if (e.shiftKey) {
                            if (e.target === $listFocusables[0]) {
                                $listFocusables[$listFocusables.length - 1].focus();
                                e.preventDefault();
                            }
                        } else {
                            // tab on last element focusable => focus on first
                            if (e.target === $listFocusables[$listFocusables.length - 1]) {
                                $listFocusables[0].focus();
                                e.preventDefault();
                            }
                        }
                    }

                    // tab outside modal => put it in focus
                    if (e.keyCode === 9 && $listFocusables.indexOf(e.target) === -1) {
                        e.preventDefault();
                        $listFocusables[0].focus();
                    }
                }
            }, true);
        });
        document.removeEventListener('DOMContentLoaded', onLoad);
    };

    document.addEventListener('DOMContentLoaded', onLoad);
})(document);
(function (root, factory) {
    if ( typeof define === 'function' && define.amd ) {
        define([], factory(root));
    } else if ( typeof exports === 'object' ) {
        module.exports = factory(root);
    } else {
        root.vanillaTabScroller = factory(root);
    }
})(typeof global !== 'undefined' ? global : this.window || this.global, function (root) {

    'use strict';

    //
    // Variables
    //

    var vanillaTabScroller = {}; // Object for public APIs
    var supports = 'querySelector' in document && 'addEventListener' in root; // Feature test
    var settings, eventTimeout;

    // Default settings
    var defaults = {
        scroller: '[data-tabs-scroller]',
        wrapper: '[data-tabs-wrapper]',
        tabs: '[data-tabs]',
        tab: '[data-tab]',
        tabsButtonPrevClass: 'pwt-tabs-scroller-prev',
        tabsButtonNextClass: 'pwt-tabs-scroller-next',
        tabsOverflowClass: 'has-tabs-overflow',
        tabsOverflowLeftClass: 'has-tabs-left-overflow',
        tabsOverflowRightClass: 'has-tabs-right-overflow',
        tabsScrollAmount: 0.8,
        tabsAnimationSpeed: 400,

        // Callbacks
        beforeSetWidth: function () {},
        afterSetWidth: function () {}
    };


    //
    // Methods
    //

    /**
     * A simple forEach() implementation for Arrays, Objects and NodeLists.
     * @private
     * @author Todd Motto
     * @link   https://github.com/toddmotto/foreach
     * @param {Array|Object|NodeList} collection Collection of items to iterate
     * @param {Function}              callback   Callback function for each iteration
     * @param {Array|Object|NodeList} scope      Object/NodeList/Array that forEach is iterating over (aka `this`)
     */
    var forEach = function ( collection, callback, scope ) {
        if ( Object.prototype.toString.call( collection ) === '[object Object]' ) {
            for ( var prop in collection ) {
                if ( Object.prototype.hasOwnProperty.call( collection, prop ) ) {
                    callback.call( scope, collection[prop], prop, collection );
                }
            }
        } else {
            for ( var i = collection.length - 1; i >= 0; i-- ) {
                callback.call( scope, collection[i], i, collection );
            }
        }
    };

    /**
     * Merge defaults with user options
     * @private
     * @param {Object} defaults Default settings
     * @param {Object} options User options
     * @returns {Object} Merged values of defaults and options
     */
    var extend = function () {

        // Variables
        var extended = {};
        var deep = false;
        var i = 0;
        var length = arguments.length;

        // Check if a deep merge
        if ( Object.prototype.toString.call( arguments[0] ) === '[object Boolean]' ) {
            deep = arguments[0];
            i++;
        }

        // Merge the object into the extended object
        var merge = function (obj) {
            for ( var prop in obj ) {
                if ( Object.prototype.hasOwnProperty.call( obj, prop ) ) {
                    // If deep merge and property is an object, merge properties
                    if ( deep && Object.prototype.toString.call(obj[prop]) === '[object Object]' ) {
                        extended[prop] = extend( true, extended[prop], obj[prop] );
                    } else {
                        extended[prop] = obj[prop];
                    }
                }
            }
        };

        // Loop through each object and conduct a merge
        for ( ; i < length; i++ ) {
            var obj = arguments[i];
            merge(obj);
        }

        return extended;

    };

    /**
     * Get the closest matching element up the DOM tree.
     * @private
     * @param  {Element} elem     Starting element
     * @param  {String}  selector Selector to match against
     * @return {Boolean|Element}  Returns null if not match found
     */
    var getClosest = function ( elem, selector ) {

        // Element.matches() polyfill
        if (!Element.prototype.matches) {
            Element.prototype.matches =
                Element.prototype.matchesSelector ||
                Element.prototype.mozMatchesSelector ||
                Element.prototype.msMatchesSelector ||
                Element.prototype.oMatchesSelector ||
                Element.prototype.webkitMatchesSelector ||
                function(s) {
                    var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                        i = matches.length;
                    while (--i >= 0 && matches.item(i) !== this) {}
                    return i > -1;
                };
        }

        // Get closest match
        for ( ; elem && elem !== document; elem = elem.parentNode ) {
            if ( elem.matches( selector ) ) return elem;
        }

        return null;

    };

    /**
     * Scroll animation
     * @private
     * @param {Object} defaults Default settings
     * @param {Object} options User options
     * @returns {Object} Merged values of defaults and options
     */

    function scrollTo(element, from, to, duration) {
        var start = from,
            change = to - start,
            currentTime = 0,
            increment = 20;

        var animateScroll = function() {
            currentTime += increment;
            var val = Math.easeInOutQuad(currentTime, start, change, duration);
            element.scrollLeft = val;
            if(currentTime < duration) {
                setTimeout(animateScroll, increment);
            }
        };
        animateScroll();
    }

    //t = current time
    //b = start value
    //c = change in value
    //d = duration
    Math.easeInOutQuad = function (t, b, c, d) {
        t /= d/2;
        if (t < 1) return c/2*t*t + b;
        t--;
        return -c/2 * (t*(t-2) - 1) + b;
    };


    /**
     * Setup and active
     * @private
     */

    var setup = function() {

        // Find gallery and return if not found
        var tabScroller = document.querySelectorAll(settings.scroller);
        if ( !tabScroller ) return;

        forEach(tabScroller, function (value) {

            // Prev button
            var beforeButton = document.createElement('button');
            beforeButton.setAttribute('type', 'button');
            beforeButton.innerHTML = 'prev';
            beforeButton.classList.add(settings.tabsButtonPrevClass);
            value.parentNode.insertBefore(beforeButton, value.parentNode.firstChild);

            // Next button
            var afterButton = document.createElement('button');
            afterButton.setAttribute('type', 'button');
            afterButton.innerHTML = 'next';
            afterButton.classList.add(settings.tabsButtonNextClass);
            value.parentNode.appendChild(afterButton);

            // scroll to current tab
            setTimeout(function() {
                vanillaTabScroller.scrollToTab(value.querySelector('.is-active'), value);
            }, settings.tabsAnimationSpeed);

            // Run on scrolling the tab container
            value.addEventListener('scroll', function() {
                if ( !eventTimeout ) {
                    eventTimeout = setTimeout(function() {
                        eventTimeout = null; // Reset timeout
                        vanillaTabScroller.tabsCalculateScroll();
                    }, 200);
                }
            });

            // Find gallery and return if not found
            var tabs = value.querySelectorAll(settings.tab);
            if ( !tabs ) return;

            forEach(tabs, function (elem) {
                elem.addEventListener('click', function() {
                    vanillaTabScroller.scrollToTab(elem, value);
                });
            });

        });

        vanillaTabScroller.tabsCalculateScroll();

    };


    // Calculate wether there is a scrollable area and apply classes accordingly
    vanillaTabScroller.tabsCalculateScroll = function(options) {

        // Merge user options with existing settings or defaults
        var localSettings = extend(settings || defaults, options || {});

        // Find gallery and return if not found
        var scroller = document.querySelectorAll(localSettings.scroller);
        if ( !scroller ) return;

        forEach(scroller, function (value) {

            // Variables
            var tabscroller = value,
                tabsWidth = tabscroller.querySelector(settings.tabs).offsetWidth,
                scrollerWidth = tabscroller.clientWidth,
                scrollLeft = tabscroller.scrollLeft,
                wrapper = tabscroller.parentNode;

            // Show / hide buttons
            if (tabsWidth > scrollerWidth) {
                wrapper.classList.add(settings.tabsOverflowClass);
            } else {
                wrapper.classList.remove(settings.tabsOverflowClass);
            }

            // "Activate" left button
            if ((tabsWidth > scrollerWidth) && (scrollLeft > 0)) {
                wrapper.classList.add(settings.tabsOverflowLeftClass);
            }

            // "Activate" right button
            if ((tabsWidth > scrollerWidth)) {
                wrapper.classList.add(settings.tabsOverflowRightClass);
            }

            // "Deactivate" left button
            if ((tabsWidth <= scrollerWidth) || (scrollLeft <= 0)) {
                wrapper.classList.remove(settings.tabsOverflowLeftClass);
            }

            // "Deactivate" right button
            if ((tabsWidth <= scrollerWidth) || (scrollLeft >= (tabsWidth - scrollerWidth))) {
                wrapper.classList.remove(settings.tabsOverflowRightClass);
            }
        });
    };


    // Calculate the amount of scrolling to do
    vanillaTabScroller.calculateScroll =  function(wrapper, direction) {

        // Variables
        var tabsWidth = wrapper.querySelector(settings.tabs).offsetWidth,
            scrollerWidth = wrapper.querySelector(settings.scroller).clientWidth,
            scrollLeft = wrapper.querySelector(settings.scroller).scrollLeft,
            scroll;

        // Left button (scroll to right)
        if ( direction == 'prev') {
            scroll = scrollLeft - (scrollerWidth * settings.tabsScrollAmount);
            if (scroll < 0 ) {
                scroll = 0;
            }
        }

        // Right button (scroll to left)
        if ( direction == 'next') {
            scroll = scrollLeft + (scrollerWidth * settings.tabsScrollAmount);
            if (scroll > (tabsWidth - scrollerWidth) ) {
                scroll = tabsWidth - scrollerWidth;
            }
        }

        // Animate the scroll
        scrollTo(wrapper.querySelector(settings.scroller), scrollLeft, scroll, settings.tabsAnimationSpeed);
    };


    // Scroll active tab into screen
    vanillaTabScroller.scrollToTab = function(element, scroller) {

        if (!element) return;

        var positionLeft = element.offsetLeft,
            positionRight = positionLeft + element.offsetWidth,

            parentPaddingLeft = parseInt(window.getComputedStyle(scroller.parentNode, null).getPropertyValue('padding-left'), 10),
            parentPaddingRight = parseInt(window.getComputedStyle(scroller.parentNode, null).getPropertyValue('padding-right'), 10),

            scrollerOffset = scroller.scrollLeft,
            scrollerWidth = scroller.clientWidth,
            scroll;


        // When item falls of on the right side
        if ( positionRight > (scrollerOffset + scrollerWidth) ) {
            scroll = scrollerOffset + ((positionRight - (scrollerWidth + scrollerOffset)) + (parentPaddingRight));
        }

        // When item falls of on the left side
        if ( positionLeft < scrollerOffset ) {
            scroll = scrollerOffset - ((scrollerOffset - positionLeft) + (parentPaddingLeft));
        }

        if (!scroll) return;

        // Animate the scroll
        scrollTo(scroller, scrollerOffset, scroll, settings.tabsAnimationSpeed);
    };


    /**
     * Handle toggle click events
     * @private
     */
    var clickHandler = function (event) {

        // Don't run if right-click or command/control + click
        if ( event.button !== 0 || event.metaKey || event.ctrlKey ) return;

        // Check if event target is a tab toggle
        var wrapper = getClosest( event.target, settings.wrapper );
        if ( !wrapper ) return;

        if (event.target.className == settings.tabsButtonPrevClass) {
            vanillaTabScroller.calculateScroll(wrapper, 'prev');
        }
        if (event.target.className == settings.tabsButtonNextClass) {
            vanillaTabScroller.calculateScroll(wrapper, 'next');
        }

    };


    /**
     * On window scroll and resize, only run events at a rate of 15fps for better performance
     * @private
     * @param  {Function} eventTimeout Timeout function
     * @param  {Object} settings
     */
    var resizeThrottler = function () {
        if ( !eventTimeout ) {
            eventTimeout = setTimeout(function() {
                eventTimeout = null; // Reset timeout

                vanillaTabScroller.tabsCalculateScroll();

            }, 200);
        }
    };


    /**
     * Destroy the current initialization.
     * @public
     */
    vanillaTabScroller.destroy = function () {

        // If plugin isn't already initialized, stop
        if ( !settings ) return;

        // Remove event listeners
        document.removeEventListener('click', clickHandler, false);
        root.removeEventListener('resize', resizeThrottler, false);

        // Reset variables
        settings = null;

    };


    /**
     * Initialize vanillaTabScroller
     * @public
     * @param {Object} options User settings
     */
    vanillaTabScroller.init = function ( options ) {

        // feature test
        if ( !supports ) return;

        // Destroy any existing initializations
        vanillaTabScroller.destroy();

        // Merge user options with defaults
        settings = extend( defaults, options || {} );

        // Listen
        document.addEventListener('click', clickHandler, false);
        root.addEventListener('resize', resizeThrottler, false);

        // Run on default
        setup();

    };


    //
    // Public APIs
    //

    return vanillaTabScroller;

});

var pwtImage = (function () {

    var pwtImage = {},
        croppers = [],
        targetId = '',
        iFrameLink = '',
        wysiwyg = false,
        resultFile = '',
        altText = '',
        captionText = '',
        uploadImage = '',
        imageLimit = 100,
        showImageDirect = 0,
        rootPath = '',
        viewMode = 1,
        sessionValue;

    /**
     * Initialise PWT Image
     */
    pwtImage.initialise = function () {
        jQuery('.js-edit-image-button').on('click', function() {
            jQuery('.js-image-cropper').removeClass('is-hidden');
            jQuery('.js-image-preview').addClass('is-hidden');
        });

        jQuery('.js-select-existing').on('click', function() {
            jQuery('[href=#select]').trigger('click');
        });

        jQuery('.js-upload-new').on('click', function() {
            jQuery('[href=#upload]').trigger('click');
        });

        var options = Joomla.getOptions('PWTImageConfig');
        sessionValue = options.sessionToken;
    };

    /**
     * Initialise the drag and drop area
     */
    pwtImage.initialiseDragnDrop = function () {
        var dragZone  = jQuery('#js-dragarea');

        dragZone.on('dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();

            dragZone.addClass('hover');

            return false;
        });

        // Notify user when file is over the drop area
        dragZone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();

            dragZone.addClass('hover');

            return false;
        });

        dragZone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dragZone.removeClass('hover');

            return false;
        });

        dragZone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();

            dragZone.removeClass('hover');

            var files = e.originalEvent.target.files || e.originalEvent.dataTransfer.files;

            if (!files.length) {
                return;
            }

            var element = '.pwt-content';

            // Store the file into the form
            pwtImage.createPreview(element, files);

            // Clean up after the upload
            pwtImage.cleanUpAfterUpload(element);

            if (showImageDirect) {
                pwtImage.directToCanvas(element);
            }
        });
    };

    /**
     * Creates another image selector instance.
     * @param element The Add another image button
     */
    pwtImage.addRepeatImage = function (element) {
        // Get a new unique ID
        var modalId = new Date().getTime();

        // Change the ID on the another image button
        var addNewButton = jQuery(element).clone();
        addNewButton.prop('id', 'addmore' + modalId);

        // Duplicate the controls
        var imageField = jQuery(element).parent();

        // Duplicate the first image block
        var imageBlock = imageField.first().clone();

        // Get the current ID value
        var currentId = imageBlock.children(':first').prop('id').split('_')[0];

        // Replace all IDs with a new ID
        imageBlock.prop('id', modalId);

        // Set the required IDs on the Select button
        var selectButton = imageBlock.children("[id^=label_modal_]");
        selectButton.prop('id', 'label_modal_' + modalId);
        var newOnClick = selectButton.attr('onclick').replace('setTargetId(\'' + currentId, 'setTargetId(\'' + modalId);
        newOnClick = newOnClick.replace('modalId=' + currentId, 'modalId=' + modalId);
        selectButton.attr('onclick', newOnClick);

        // Set the other required IDs
        imageBlock.children('#' + currentId + '_preview').prop('id', modalId + '_preview').html('');
        imageBlock.children('#' + currentId + '_value').prop('id', modalId + '_value');
        imageBlock.children('#' + currentId + '_clear').prop('id', modalId + '_clear')
            .addClass('hidden').prop('onclick', 'pwtImage.clearImage(\'' + modalId + '\');');
        imageBlock.children('#addmore' + currentId).prop('id', 'addmore' + modalId);

        // Add the new image to the DOM
        imageField.first().parent().append(imageBlock);
    };

    /**
     * Prepare everything to allow a new image upload
     * @param id
     */
    pwtImage.prepareUpload = function (id) {
        jQuery('#' + id + ' button.js-button-save-new').prop('disabled', true);
        jQuery('#' + id + ' .pwt-image-localfile').val('');
        jQuery('#' + id + ' .pwt-image-targetfile').val('');
        jQuery('#' + id + ' .pwt-message').removeClass('is-visible');
    };

    /**
     * Set the ID of the image controls to use
     *
     * @param id The unique ID of the image controls to use.
     */
    pwtImage.setTargetId = function (id) {
        targetId = id;
    };

    /**
     * Set the iFrame link to empty out the iFrame
     */
    pwtImage.setIframeLink = function (link) {
        iFrameLink = link;
    };

    /**
     * Set the root path of the installation
     */
    pwtImage.setRootPath = function (root) {
        rootPath = root;
    };

    /**
     * Set if we are called from a WYSIWYG editor
     */
    pwtImage.setWysiwyg = function (value) {
        wysiwyg = value;
    };

    /**
     * Set if we are called from a WYSIWYG editor
     */
    pwtImage.setViewMode = function (value) {
        viewMode = value;
    };

    /**
     * Set if the image should be opened in the canvas instead of preview mode
     */
    pwtImage.showImageDirect = function (value) {
        showImageDirect = value;
    };

    /**
     * Return the ID of the image controls to use
     */
    pwtImage.getTargetId = function () {
        return targetId;
    };

    /**
     * Store the image on the server.
     *
     * @param id
     * @param tokenValue
     * @param createNew
     */
    pwtImage.saveImage = function (id, tokenValue, createNew) {
        var cropper = false;

        // Check if we have an existing cropper
        if (croppers[id] !== undefined) {
            cropper = croppers[id];
        }

        if (createNew === 'undefined') {
            createNew = false;
        }

        var postUrl = jQuery('#post-url').val(),
            image = jQuery('#' + id + '_upload')[0].files[0],
            crop = jQuery('#' + id + ' .js-pwt-image-data').val(),
            width = jQuery('#' + id + ' .js-pwt-image-width').val(),
            ratio = jQuery('#' + id + ' .js-pwt-image-ratio').val(),
            keepOriginal = jQuery('#' + id + ' .js-pwt-image-keepOriginal').is(':checked'),
            sourcePath = jQuery('#' + id + ' .js-pwt-image-sourcePath').val(),
            subPath = jQuery('#' + id + ' .js-pwt-image-subPath').val(),
            localfile = jQuery('#' + id + ' .js-pwt-image-localfile').val(),
            targetfile = jQuery('#' + id + ' .js-pwt-image-targetfile').val(),
            backgroundColor = jQuery('#' + id + ' .js-pwt-image-backgroundColor').val(),
            origin = jQuery('#' + id + ' .js-pwt-image-origin').val(),
            data = new FormData();

        // Check if an actual file has been uploaded
        if (uploadImage && image === undefined) {
            image = uploadImage[0];
        }

        // Check if an alt text has been set by the image selector, if not we take the one from the edit tab
        if (altText === '') {
            altText = jQuery('#' + id + ' #alt').val();
        }

        // Check if a caption text has been set by the image selector, if not we take the one from the edit tab
        if (captionText === '') {
            captionText = jQuery('#' + id + ' #caption').val();
        }

        // Get the store folder
        switch (jQuery('#' + id + '_destinationFolder').val()) {
            case 'select':
                subPath = jQuery('#' + id + '_selectedFolderOptions').val();
                break;
        }

        // Check if we have a width from the field definition, if not we take the width from the cropped area
        if (Number(width) === 0) {
            if (cropper) {
                width = cropper.getCropBoxData().width;
            }
            else {
                var img = new Image();

                img.onload = function() {
                    width = this.width;
                };

                img.src = localfile;
            }
        }

        // Add the form data
        data.append('option', 'com_pwtimage');
        data.append('task', 'image.processImage');
        data.append('format', 'json');
        data.append('pwt-image-localFile', localfile);
        data.append('pwt-image-targetFile', targetfile);
        data.append('alt', altText);
        data.append('caption', captionText);
        data.append('pwt-image-data', crop);
        data.append('pwt-image-width', width);
        data.append('pwt-image-ratio', ratio);
        data.append('pwt-image-keepOriginal', keepOriginal);
        data.append('pwt-image-sourcePath', sourcePath);
        data.append('pwt-image-subPath', subPath);
        data.append('pwt-image-backgroundColor', backgroundColor);
        data.append('pwt-image-origin', origin);
        data.append('image', image);

        // Find the target ID
        var targetId = pwtImage.getTargetId();

        // Try to upload and process the image
        try {
            jQuery.ajax({
                type: 'POST',
                data: data,
                contentType: false,
                url: postUrl,
                cache: false,
                processData: false,
                async: false,
                headers: {"X-CSRF-TOKEN": tokenValue},
                success: function (response) {
                    if (response instanceof Object === false) {
                        // Check if we have a know error message
                        var friendlyMessage = findErrorMessage(response);

                        if (friendlyMessage.length === 0) {
                            friendlyMessage = response;
                        }

                        renderMessage({error: [friendlyMessage]}, true);

                        throw false;
                    }

                    if (response.message) {
                        renderMessage({warning: [response.message]});
                    }

                    if (response.messages) {
                        renderMessage(response.messages);
                    }

                    // Check if there are multiple images returned, if so, take the first one
                    resultFile = response.data;

                    if (!wysiwyg) {
                        resultFile = resultFile.split(',');

                        if (resultFile.length > 0) {
                            resultFile = resultFile[0];
                        }

                        window.parent.jQuery('#' + targetId + '_value').val(resultFile);
                        window.parent.jQuery('#' + targetId + '_preview').html('<img src="' + rootPath + resultFile + '" />');
                        window.parent.jQuery('#' + targetId + '_clear').removeClass('hidden');
                    }

                    if (createNew) {
                        jQuery('#' + id + ' .pwt-message').addClass('is-visible');
                        jQuery('#' + id + ' span.has_folder').html(response.data);
                    }

                    // All done, self-destruction is imminent if we don't want to create a new image
                    if (cropper) {
                        cropper.destroy();
                    }

                    var imageCanvas = document.getElementById(id + '_js-pwtimage-image');

                    imageCanvas.removeAttribute('src');

                    if (!createNew) {
                        pwtImage.closeModal();
                    }
                },
                error: function (response) {
                    renderMessage({error: [Joomla.JText._('COM_PWTIMAGE_SAVE_FAILED', 'There was a problem to save the file')]});
                    console.log('Image upload failed: ' + response.responseText);

                    throw false;
                }
            });

            // Switch back to upload tab if user wants to create another image
            if (createNew) {
                // Clear variables
                altText = '';
                captionText = '';
                resultFile = '';
                jQuery('#' + id + ' .js-pwt-image-localfile').val('');
                jQuery('#' + id + '_upload').replaceWith(jQuery('#' + id + '_upload').val('').clone(true));

                // Clean up the crop data
                jQuery('#' + id + ' .js-pwt-image-data').val('');

                // Reload the images
                if (subPath.length === 0) {
                    sourcePath = jQuery('#' + id + ' .js-sourcePath').text();
                    subPath = sourcePath.substring(0, (sourcePath.length - 1));
                }

                pwtImage.loadFolder('#' + id, subPath, 'select', tokenValue);

                // Reset the Edit page
                var fulltab = jQuery('.pwt-fulltab-message');
                fulltab.removeClass('is-hidden');
                fulltab.next().addClass('is-hidden');

                jQuery('[href="#select"]').trigger('click');
            }
        }
        catch (exception) {
            return false;
        }

        pwtImage.cancelImage(id);

        return true;
    };

    /**
     * Clears the current image and preview.
     * @param id  The ID of the image
     */
    pwtImage.clearImage = function (id) {
        jQuery('#' + id + '_value').val('');
        jQuery('#' + id + '_preview img').prop('src', '');
        jQuery('#' + id + '_clear').addClass('hidden');
        jQuery('#' + id + '_js-pwtimage-image').prop('src', '');
    };

    /**
     * Control the toolbar actions
     *
     * @param element  The element that has been clicked.
     */
    pwtImage.imageToolbar = function (element) {
        var id = getParentId(element),
            data = jQuery(element).data();

        // Check if the button is active
        if (jQuery(this).prop('disabled') || jQuery(this).hasClass('disabled')) {
            return;
        }

        // Check if we have a valid cropper
        if (croppers[id] === undefined) {
            return;
        }

        // Instantiate the cropper
        var cropper = croppers[id];

        // Set some values if needed
        switch (data.method) {
            case 'scaleX':
            case 'scaleY':
                cropper[data.method](data.option);
                jQuery(element).data('option', data.option === 1 ? -1 : 1);
                break;
            case 'rotate':
            case 'zoom':
                cropper[data.method](data.option);
                break;
            case 'ratio':
                var ratio = data.option;

                if ((typeof ratio === 'string') && ratio.indexOf('/') > 0) {
                    var ratios = data.option.split('/');
                    ratio = ratios[0] / ratios[1];
                }
                cropper.setAspectRatio(ratio);
                jQuery('#' + id + ' .js-pwt-image-ratio').val(data.option);
                break;
        }
    };

    /**
     * Open the server image selection page
     *
     * @param element  The element that has been clicked.
     */
    pwtImage.openImage = function (element) {
        // Get the parent ID
        var id = getParentId(element);

        jQuery('#' + id + ' .js-pwt-image-toolbar').hide();
        jQuery('#' + id + ' .js-cropper-container').hide();
        jQuery('form.js-image-form > div.pull-right').hide();
    };

    /**
     * Get the parent ID of a given element
     *
     * @param element
     * @returns string|boolean
     *
     * @todo Check with multiple blocks
     */
    function getParentId(element) {
        var identifier = jQuery(element).closest('.js-pwtimage-id').prop('id');

        if (identifier === undefined) {
            console.log('Cannot find parent ID for element');
            console.log(element);

            return false;
        }

        return identifier;
    }

    /**
     * Load subfolders for a selected folder
     *
     * @param element
     * @param folder
     * @param target
     * @param tokenValue
     * @returns {boolean}
     */
    pwtImage.loadFolder = function (element, folder, target, tokenValue) {
        var id = getParentId(element);
        var data = new FormData();
        var postUrl = jQuery('#post-url').val();

        // Add the form data
        data.append('option', 'com_pwtimage');
        data.append('task', 'image.loadFolder');
        data.append('format', 'json');
        data.append('folder', folder);

        // Load the subfolders of given folder
        jQuery.ajax({
            type: 'POST',
            data: data,
            url: postUrl,
            contentType: false,
            cache: false,
            processData: false,
            headers: {"X-CSRF-TOKEN": tokenValue},
            success: function (response) {
                try {
                    if (response.message) {
                        console.log('Failed to load subfolders from folder ' + folder + '. Message: ' + response.message);
                        renderMessage({warning: [response.message]});

                        return false;
                    }

                    if (response.messages) {
                        renderMessage(response.messages);

                        return false;
                    }

                    if (response.data === undefined)
                    {
                        return false;
                    }
                }
                catch (e) {
                    console.log(e.message);
                }

                var link = [];

                if (response.data.folders) {
                    var folderItems;
                    var folderPath = [];
                    var structure = '';

                    if (folder.indexOf('/') > 0 || folder.length > 1) {
                        folderItems = folder.split('/');
                    }

                    // Construct the breadcrumb path
                    jQuery(folderItems).each(function (index, folderItem) {
                        structure += folderItem;

                        if (structure.length > 0) {

                            folderPath[index + 1] = '<a onclick="pwtImage.loadFolder(\'.pwt-gallery__items--folders\', \'/' + structure + '\', \'' + target + '\', \'' + tokenValue + '\'); return false;"><span class="icon-folder-2"></span>' + folderItem + '</a>';
                            structure += '/';
                        }
                    });

                    jQuery('#' + target + ' .js-breadcrumb').html(folderPath.join(' '));

                    // Collect all folders to show
                    jQuery(response.data.folders).each(function (index, item) {
                        var itemPath = item;

                        if (folder !== '/') {
                            itemPath = folder + '/' + item;
                        }

                        link.push('<div class="pwt-gallery__item"><a onclick="pwtImage.loadFolder(\'.pwt-gallery__items--folders\', \'' + itemPath + '\', \'' + target + '\', \'' + tokenValue + '\'); return false;">' +
                            '<div class="pwt-gallery__item__content">' +
                            '<span class="pwt-gallery__item__icon icon-folder-2"></span>' +
                            '<span class="pwt-gallery__item__title">' + item + '</span>' +
                            '</div>' +
                            '</a></div>');
                    });

                    // Add the folders
                    var itemFolders = jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--folders');
                    itemFolders.html('');

                    if (link.length) {
                        itemFolders.html(link.join(' '));
                    }
                }

                // Add the files
                var pagination = [];

                if (response.data.files) {
                    // Setup pagination
                    var pages = Math.ceil(response.data.files.length / imageLimit);

                    for (var page = 1; page <= pages; page++) {
                        pagination.push('<a onclick="pwtImage.showMoreImages(\'.pwt-gallery__items--images\', ' + page + ', \'' + target + '\');">' + page + '</a>');
                    }

                    // Store the data in localStorage
                    addToLocalStorage(id, 'files', response.data.files);
                    addToLocalStorage(id, 'basePath', response.data.basePath);

                    // Get the list of images to display
                    var files = response.data.files.slice(0, imageLimit);

                    // Prepare the images for display
                    link = prepareImages(files, folder, target);

                    // Add the files
                    var itemImages = jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--images');
                    itemImages.html('');

                    if (link.length) {
                        itemImages.html(link.join(' '));
                    }

                    // Add the pagination
                    var paginationBar = jQuery('#' + id + ' #' + target + ' .pwt-pagination');
                    paginationBar.html('');

                    if (pagination.length) {
                        paginationBar.html('<div class="pwt-pagination__pages">' + pagination.join(' ') + '</div>');
                    }

                    jQuery('.pwt-gallery__item img').each(function () {
                        jQuery(this).load(function () {
                            if (this.width > this.height) {
                                jQuery(this).addClass('is-landscape');
                            }
                        });
                    });
                }

                // Filter the page
                pwtImage.selectFilter(jQuery('#selectFilter').val());
            },
            error: function (response) {
                console.log('Failed to load folder: ' + response.responseText);
                console.log('Response code: ' + response.status + ' ' + response.statusText);
            }
        });

        return false;
    };

    /**
     * Load the folders for the select picker
     *
     * @param folder
     * @param tokenValue
     * @returns {boolean}
     */
    pwtImage.loadSelectFolders = function (folder, tokenValue) {
        var data = new FormData();
        var postUrl = jQuery('#post-url').val();

        // Add the form data
        data.append('option', 'com_pwtimage');
        data.append('task', 'image.loadSelectFolders');
        data.append('format', 'json');
        data.append('sourcePath', folder);

        // Load the folders
        jQuery.ajax({
            type: 'POST',
            data: data,
            url: postUrl,
            contentType: false,
            cache: false,
            processData: false,
            headers: {"X-CSRF-TOKEN": tokenValue},
            success: function (response) {
                // Empty the list
                choicesFolder._clearChoices();

                // Add the folders
                response.data[0].forEach(function(item)
                {
                    choicesFolder._addChoice(item, item);
                });

                // Set the default value
                choicesFolder.setValueByChoice('/');
            },
            error: function (response) {
                console.log('Failed to load folder: ' + response.responseText);
                console.log('Response code: ' + response.status + ' ' + response.statusText);
            }
        });

        return false;
    };

    /**
     * Pagination class
     *
     * @param element
     * @param page
     * @param target
     */
    pwtImage.showMoreImages = function (element, page, target) {
        var id = getParentId(element),
            storedFiles = getFromLocalStorage(id, 'files'),
            basePath = getFromLocalStorage(id, 'basePath'),
            start = ((page === 1) ? 0 : (page - 1) * imageLimit),
            end = page * imageLimit,
            files = storedFiles.slice(start, end);

        // Prepare the images for display
        var link = prepareImages(files, basePath, target);

        // Add the files
        var itemImages = jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--images');
        itemImages.html('');

        if (link.length) {
            itemImages.html(link.join(' '));
        }

        // Filter the page
        pwtImage.selectFilter(jQuery('#selectFilter').val(), page);
    };

    /**
     * Prepare a list of images for display
     */
    function prepareImages(files, folder, target) {
        var link = [];

        // Clean up the folder as the rootPath ends with a slash and the folder starts with a slash
        if (folder.substring(0, 1) === '/') {
            folder = folder.substring(1) + '/';
        }

        jQuery(files).each(function (index, item) {
            var itemPath = rootPath + folder + item;

            link.push(getImageElement(item, itemPath, target));

        });

        return link;
    }

    /**
     * Construct an image element
     */
    function getImageElement(item, itemPath, target) {
        var imageElement = '',
            imageName = itemPath.substring(itemPath.lastIndexOf('/') + 1);

        switch (target) {
            case 'select':
                imageElement = '<div class="pwt-gallery__item">' +
                    '<a onclick="return pwtImage.previewImage(\'.pwt-gallery__items--images\', \'' + itemPath + '\');" ' +
                    'title="' + imageName + '">' +
                    '<div class="pwt-gallery__item__image">' +
                    '<div class="pwt-gallery__item__center">' +
                    '<img src="' + itemPath + '" alt="' + baseName(item) + '" />' +
                    '</div>' +
                    '<div class="pwt-gallery__item__imagename">' +
                    imageName +
                    '</div>' +
                    '</div>' +
                    '</a>' +
                    '</div>';
                break;
        }

        return imageElement;
    }

    /**
     * Find a known error message
     *
     * @param response  The response message to analyze
     *
     * @returns {string}
     */
    function findErrorMessage(response) {
        // 'Fatal error: Allowed memory size';
        var pattern = 'Allowed memory size';

        if (response.indexOf(pattern) !== -1) {
            return Joomla.JText._('COM_PWTIMAGE_ERROR_ALLOWED_MEMORY_SIZE');
        }

        return '';
    }

    /**
     * This adds a file on the server to the canvas for cropping
     *
     * @param element  The page element to find the ID for.
     * @param file     The selected image on the server
     * @param image
     * @param upload
     *
     * @returns {boolean}
     */
    pwtImage.addImageToCanvas = function (element, file, image, upload) {
        var id = getParentId(element);

        if (upload === undefined || upload === null) {
            upload = false;
        }

        // Remove any existing cropper boxes
        jQuery('#' + id + ' div.pwt-body div.cropper-container > div.cropper-container').remove();

        // Get the ratio
        var ratio = jQuery('#' + id + ' .js-pwt-image-ratio').val(),
            ratioSplit = ratio.split('/');

        // Get the image field
        if (!upload) {
            image = document.getElementById(id + '_js-pwtimage-image');
        }

        // Instantiate the cropper
        var cropper = new Cropper(image, {
            aspectRatio: ratioSplit[0] / ratioSplit[1],
            viewMode: viewMode,
            crop: function (e) {
                var json = [
                    '{"x":' + e.detail.x,
                    '"y":' + e.detail.y,
                    '"height":' + e.detail.height,
                    '"width":' + e.detail.width,
                    '"rotate":' + e.detail.rotate,
                    '"scaleX":' + e.detail.scaleX,
                    '"scaleY":' + e.detail.scaleY + '}'
                ].join();

                jQuery('#' + id + ' .js-pwt-image-data').val(json);
            },
            ready: function () {
                var imageData = cropper.getImageData();
                cropper.setCropBoxData(
                    {
                        top: 0,
                        width: imageData.width
                    }
                )
            }
        });

        // Add the cropper to the list of croppers
        if (upload) {
            croppers[id] = cropper;
            cropper.init();

            // Set the image value in the form
            window.parent.jQuery('#' + id + '_value').prop('value', file.name);
        }
        else {
            // Replace the image
            cropper.replace(file);
            croppers[id] = cropper;
        }

        // Hide the message
        jQuery('#' + id + ' .pwt-fulltab-message').addClass('is-hidden');

        // Enable the Save & new button
        jQuery('#' + id + ' button.js-button-save-new').prop('disabled', false);

        jQuery('#' + id + ' .js-image-info').removeClass('is-hidden');

        // Make the edit tab visible
        jQuery('[href="#edit"]').trigger('click');
        jQuery('#' + id + ' .js-button-image').removeClass('hidden');

        return false;
    };

    /**
     * Get the basename of a filename with path
     *
     * @param str
     * @returns {string}
     */
    function baseName(str) {
        var base = str.substring(str.lastIndexOf('/') + 1);

        if (base.lastIndexOf(".") !== -1) {
            base = base.substring(0, base.lastIndexOf("."));
        }

        return base;
    }

    /**
     * Close the modal window
     */
    pwtImage.closeModal =  function() {
        if (wysiwyg) {
            if (resultFile) {
                jQuery('form.js-image-form #formPath').val(resultFile);
            }

            if (altText) {
                jQuery('form.js-image-form #alt').val(altText);
            }

            if (captionText) {
                jQuery('form.js-image-form #caption').val(captionText);
            }

            jQuery('form.js-image-form #layout').val('close');
            jQuery('form.js-image-form').submit();
        }
        else {
            window.parent.jQuery('iframe#pwtImageFrame-' + targetId).prop('src', iFrameLink);
            window.parent.jQuery('#js-modal-close').click();
        }
    };

    /**
     * Show the applicable destination option
     */
    pwtImage.setDestination = function (element) {
        var id = getParentId(element);

        if (choicesDestination !== undefined) {
            switch (choicesDestination.getValue().value) {
                case 'default':
                    jQuery('#' + id + '_enterFolder').addClass('is-visible').removeClass('is-hidden');
                    jQuery('#' + id + '_subPath').prop('disabled', true);
                    jQuery('#' + id + '_selectFolder').addClass('is-hidden').removeClass('is-visible');
                    break;
                case 'select':
                    jQuery('#' + id + '_enterFolder').addClass('is-hidden').removeClass('is-visible');
                    jQuery('#' + id + '_selectFolder').addClass('is-visible').removeClass('is-hidden');
                    break;
                case 'custom':
                    jQuery('#' + id + '_enterFolder').addClass('is-visible').removeClass('is-hidden');
                    jQuery('#' + id + '_subPath').prop('disabled', false);
                    jQuery('#' + id + '_selectFolder').addClass('is-hidden').removeClass('is-visible');
                    break;
            }

            jQuery('#' + id + '_selectFolder').trigger('change');
        }
    };

    /**
     * Shows the alt and caption input fields for a selected gallery image
     *
     * @param element
     */
    pwtImage.imageInfo = function(element) {
        // Check if we should act on the click, only act in the sortable result div
        if (jQuery(element).closest('#js-sortable-result').length === 0) {
            return;
        }

        // Check if we are selected
        var isSelected = jQuery(element).parent().find('a').hasClass('is-selected');

        // Remove all selected borders
        jQuery('#js-sortable-result').find('a.is-selected').removeClass('is-selected');

        // Hide any existing inputs
        jQuery('#gallery-image-info .js-image-info').hide();

        // If we were selected, don't select it again
        if (isSelected) {
            // Show the Alt and Caption box
            jQuery('#gallery-image-info').hide();

            return;
        }

        // Add border to selected image
        jQuery(element).parent().find('a').addClass('is-selected');

        // Get the unique image name
        if (element.type === 'button') {
            var imageName = jQuery(element).siblings().find('img').prop('src').replace(/[\/\.]/g, '_');
        }
        else {
            var imageName = jQuery(element).find('img').prop('src').replace(/[\/\.]/g, '_');
        }

        // Check if an input exists
        if (jQuery('#gallery-image-info input[name="input_' + imageName + '_alt"]').length === 0) {
            // Create the text field image name
            var pathField = document.createElement('div');
            pathField.className = 'js-image-info ' + imageName + '_path';
            pathField.innerHTML = jQuery(element).parent().find('img').prop('src');
            jQuery('#gallery-image-info .pwt-form-group:first').append(pathField);

            // Create the alt input box with a unique name
            var altInput = document.createElement('input');
            altInput.type = 'text';
            altInput.name = 'input_' + imageName + '_alt';
            altInput.className = 'pwt-form-control js-image-info js-pwt-image-alt';
            jQuery('#gallery-image-info .pwt-form-group:nth-child(3)').append(altInput);

            // Create the caption input box with a unique name
            var captionInput = document.createElement('input');
            captionInput.type = 'text';
            captionInput.name = 'input_' + imageName + '_caption';
            captionInput.className = 'pwt-form-control js-image-info js-pwt-image-caption';
            jQuery('#gallery-image-info .pwt-form-group:nth-child(4)').append(captionInput);
        }
        else {
            // Show existing input boxes
            jQuery('#gallery-image-info div.' + imageName + '_path').show();
            jQuery('#gallery-image-info input[name="input_' + imageName + '_alt"]').show();
            jQuery('#gallery-image-info input[name="input_' + imageName + '_caption"]').show();
        }

        // Show the Alt and Caption box
        jQuery('#gallery-image-info').show();

        jQuery('#gallery-image-info input[name="input_' + imageName + '_alt"]').focus();
    };

    /**
     * Clean up after uploading an image
     *
     * @param element
     */
    pwtImage.cleanUpAfterUpload = function(element) {
        var id = getParentId(element);

        // Make the edit tab visible
        jQuery('[href="#edit"]').trigger('click');

        // Set the Save to folder selector to default
        if (choicesDestination !== undefined) {
            choicesDestination.setValueByChoice('default');
            pwtImage.setDestination('#' + id + '_destinationFolder');
        }
    };

    /**
     * Create a preview of the manual uploaded image
     *
     * @param element
     * @returns {boolean}
     */
    pwtImage.uploadImagePreview = function (element) {
        // Get the image details
        var url = window.URL || window.webkitURL;

        // Check if we have a URL
        if (url) {
            // Get the image field
            var id = getParentId(element),
                imageUpload = document.getElementById(id + '_upload');

            imageUpload.onchange = function () {
                // Create the preview
                pwtImage.createPreview(element, this.files);

                // Clean up
                pwtImage.cleanUpAfterUpload(element);

                // Make the edit tab active
                jQuery('[href="#edit"]')[0].click();

                if (showImageDirect) {
                    pwtImage.directToCanvas(element);
                }
            };
        }

        return false;
    };

    /**
     * Shortcut for adding image to canvas from preview window
     *
     * @param element
     */
    pwtImage.directToCanvas = function (element) {
        var id = getParentId(element);

        // Set the correct classes
        jQuery('#' + id + ' .js-image-preview').addClass('is-hidden');
        jQuery('#' + id + ' .js-image-cropper').removeClass('is-hidden');

        // Get the file to show
        var file = jQuery('#' + id + '_preview img').prop('src');

        // Disable the keep original size option
        jQuery('#' + id + ' #keepOriginal').prop('checked', false);

        // Add the file to the canvas
        pwtImage.addImageToCanvas(element, file);
    };

    /**
     * Create an image preview for a manual or drag and drop uploaded image
     *
     * @param element
     * @param files
     * @returns {boolean}
     */
    pwtImage.createPreview = function(element, files) {
        // Get the parent ID
        var id = getParentId(element);
        var image = jQuery('#' + id + '_preview img');

        // Check if any file has been uploaded
        if (files && files.length) {
            // Store the uploaded files
            uploadImage = files;

            // Get the values
            var file = files[0],
                imageMaxSize = jQuery('.js-pwt-image-maxsize').val(),
                dimensionSizes = jQuery('.js-pwt-image-dimensionsize');

            // Check if the image is within our maximum sizes
            if (file.size > imageMaxSize && imageMaxSize > 0) {
                window.alert(jQuery('.js-pwt-image-maxsize-message').val());

                // Clear the images
                imageUpload.value = '';
                image.removeAttribute('src');

                return false;
            }

            // Check if the image is of an image type
            if (/^image\/\w+$/.test(file.type)) {
                // Load the image
                image.prop('src', URL.createObjectURL(file));

                // Now check for dimension size
                if (image.naturalHeight > dimensionSizes.val() || image.naturalWidth > dimensionSizes.val()) {
                    // Show the error message
                    window.alert(jQuery('.js-pwt-image-maxsize-message').val());

                    // Clear the images
                    imageUpload.value = '';
                    image.removeAttribute('src');

                    return false;
                }

                // Set the filename in the Save to folder location
                var fileName = file.name.split('/').slice(-1)[0];
                jQuery('#' + id + ' #pwt-image-targetFile').val(fileName);

                // Set the width
            } else {
                window.alert(Joomla.JText._('COM_PWTIMAGE_CHOOSE_IMAGE', 'Please choose an image file.'));
            }
        }

        // Hide the message
        jQuery('#' + id + ' .pwt-message').removeClass('is-visible');

        // Show the preview
        jQuery('#' + id + ' .pwt-edit-block').removeClass('is-hidden');
        jQuery('#' + id + ' .pwt-fulltab-message').addClass('is-hidden');

        // Enable the insert button on the edit page
        jQuery('#' + id + ' .js-button-image').prop('disabled', false);

        // Show some basic info of the original file
        image.off('load').on('load', function() {
            jQuery('.js-pwt-filename').html(file.name);
            jQuery('.js-pwt-filesize').html(filesize(file.size));
            jQuery('.js-pwt-fileext').html(file.type.replace('image/', ''));
            jQuery('.js-pwt-filedimensions').html(this.naturalWidth + ' x ' + this.naturalHeight);
        });
    };

    /**
     * Add an image selected from server to the edit tab. Not in the cropper but in a div.
     *
     * @param element
     * @param file
     */
    pwtImage.previewImage = function(element, file) {
        var id = getParentId(element);

        pwtImage.cancelImage(id);

        // Get the local path of the file
        var cleanFile = file.replace(rootPath, '/');

        jQuery('#' + id + '_preview img').prop('src', file);
        jQuery('#' + id + '_js-pwtimage-image').prop('src', cleanFile);
        jQuery('#' + id + ' .js-pwt-image-localfile').val(cleanFile);

        // Make the edit tab visible
        jQuery('[href="#edit"]').trigger('click');

        // Hide the message
        jQuery('#' + id + ' .pwt-fulltab-message').addClass('is-hidden');
        jQuery('#' + id + ' .pwt-edit-block').removeClass('is-hidden');

        // Show the preview
        jQuery('#' + id + ' .js-image-preview').removeClass('is-hidden');

        // Set the folder option to select
        if (choicesDestination !== undefined) {
            // Check if the select option exists
            choicesDestination.setValueByChoice('select');
            var selected = choicesDestination.getValue().value;

            if (selected !== 'select') {
                choicesDestination.setValueByChoice('default');
            }

            pwtImage.setDestination('#' + id + '_destinationFolder');
        }

        // Remove the domain form the file
        if (file.indexOf(rootPath) === 0) {
            file = file.substring(rootPath.length - 1);
        }

        // Set the path of the image
        var basePath = jQuery('#' + id + '_selectFolder').prev().text(),
            path = file.substring(basePath.length, file.lastIndexOf('/')),
            filename = file.substring(file.lastIndexOf('/') + 1);

        var sourcePath = jQuery('#' + id + ' .js-sourcePath').text();

        if (path.lastIndexOf(sourcePath) === 0) {
            path = path.substring(sourcePath.length);
        }

        if (path.length > 1) {
            if (selected === 'select' && choicesFolder !== undefined) {
                choicesFolder.setValueByChoice(path);
            }
            else {
                jQuery('#' + id + '_subPath').val(path + '/');
            }
        }

        if (filename.length > 0) {
            jQuery('#' + id + ' #pwt-image-targetFile').val(filename);
        }

        // Enable the insert button on the edit page
        jQuery('#' + id + ' .js-button-image').prop('disabled', false);

        if (showImageDirect) {
            pwtImage.directToCanvas('.pwt-content');
        }

        // Show some basic info of the original file
        jQuery.ajax({
            type: 'POST',
            data: {
                'option': 'com_pwtimage',
                'task': 'image.loadMetaData',
                'format': 'json',
                'image': file
            },
            url: jQuery('#post-url').val(),
            headers: {"X-CSRF-TOKEN": sessionValue},
            success: function (response) {
                if (response && response.success === true) {
                    file = response.data;

                    jQuery('.js-pwt-filename').html(file.name);
                    jQuery('.js-pwt-filesize').html(filesize(file.size));
                    jQuery('.js-pwt-fileext').html(file.mime.replace('image/', ''));
                    jQuery('.js-pwt-filedimensions').html(file[0] + ' x ' + file[1]);
                }
            },
            error: function (response) {
                // Hide the info section
                console.log(response);
            }
        });
    };

    /**
     * Cancel the editing of the current image
     */
    pwtImage.cancelImage = function(id) {
        jQuery('#' + id + ' button.js-button-save-new').prop('disabled', true);
        jQuery('#' + id + ' .js-image-cropper').addClass('is-hidden');
        jQuery('#' + id + ' .js-image-info').addClass('is-hidden');
        jQuery('#' + id + ' .js-image-preview').removeClass('is-hidden');
    };

    /**
     * Filter the select list of images
     *
     * @param search The search value to filter on
     * @param page   The page that is active
     */
    pwtImage.selectFilter = function(search, page) {
        // Remove all images
        jQuery('#select .pwt-filepicker__content .pwt-gallery__items--images').html('');

        if (page === undefined) {
            page = 1;
        }

        // Initialise all variables
        var id = getParentId('#selectFilter'),
            storedFiles = getFromLocalStorage(id, 'files'),
            basePath = getFromLocalStorage(id, 'basePath'),
            filteredItems = storedFiles,
            target = 'select',
            start = ((page === 1) ? 0 : (page - 1) * imageLimit),
            end = page * imageLimit,
            pagination = [];

        // If we have a search filter, filter the images
        if (search) {
            filteredItems = filterItems(storedFiles, search);
        }

        // Get the final list of items
        var files = filteredItems.slice(start, end);

        // Create the list of images to show
        var links = prepareImages(files, basePath, target);

        // Add the files
        var itemImages = jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--images');
        itemImages.html('');

        if (links.length) {
            itemImages.html(links.join(' '));
        }

        // Setup pagination
        var pages = Math.ceil(filteredItems.length / imageLimit);

        for (page = 1; page <= pages; page++) {
            pagination.push('<a onclick="pwtImage.showMoreImages(\'.pwt-gallery__items--images\', ' + page + ', \'' + target + '\');">' + page + '</a>');
        }

        // Add the pagination
        var paginationBar = jQuery('#' + id + ' #' + target + ' .pwt-pagination');
        paginationBar.html('');

        if (pagination.length) {
            paginationBar.html('<div class="pwt-pagination__pages">' + pagination.join(' ') + '</div>');
        }
    };

    /**
     * Restore canvas as user wants to keep the original image
     */
    pwtImage.keepOriginal = function(id, element) {
        if (jQuery(element).is(':checked')) {
            pwtImage.cancelImage(id);
        }
    };

    /**
     * Array filters items based on search criteria (query)
     *
     * @param items
     * @param query
     */
    function filterItems(items, query) {
        return items.filter(function(el) {
            return el.toLowerCase().indexOf(query.toLowerCase()) > -1;
        })
    }

    /**
     * Get the filesize in a human readable string
     *
     * @param size The size in bits
     *
     * @return string Human readable string
     */
    function filesize(size) {
        var kb = (size / 1024).toFixed(2),
            mb = (size / 1048576).toFixed(2);

        return (mb >= 1) ? mb + ' MB': kb + ' KB';
    }

    /**
     * Add the data from the localStorage
     *
     * @param id The unique session ID
     * @param type The type of data to be stored
     * @param payload The data to be stored
     */
    function addToLocalStorage(id, type, payload) {
        // Get our container
        var container = JSON.parse(localStorage.getItem('pwtImage'));

        // Make sure the container is defined
        if (container === undefined || container === null) {
            container = {};
        }

        // Construct a unique key
        var key = id + '_' + type;

        // Check for any existing IDs, remove obsolete ones
        for (var storedKey in container)
        {
            if (storedKey.indexOf(type) > 0 && key !== storedKey && container.hasOwnProperty(storedKey)) {
                delete container[storedKey];
            }
        }

        // Fill the container
        container[key] = payload;

        // Store the container
        localStorage.setItem('pwtImage', JSON.stringify(container));
    }

    /**
     * Get the data from the localStorage
     */
    function getFromLocalStorage(id, type) {
        // Get our container
        var container = JSON.parse(localStorage.getItem('pwtImage'));

        // Make sure the container is defined
        if (container === undefined || container === null) {
            return [];
        }

        // Construct a unique key
        var key = id + '_' + type;

        // Check if the key exist
        if (container.hasOwnProperty(key)) {
            return container[key];
        }
    }

    /**
     * Render a Joomla system message
     */
    function renderMessage(message, local) {
        if (local === 'undefined') {
            // Render the Joomla message, first make sure there is a message container present
            var messageContainer = window.parent.document.getElementById('system-message-container');

            if (!messageContainer) {
                return true;
            }

            window.parent.Joomla.renderMessages(message);
        }
        else {
            // Render the Joomla message, first make sure there is a message container present
            var messageContainer = window.document.getElementById('system-message-container');

            if (!messageContainer) {
                return true;
            }

            window.Joomla.renderMessages(message);
        }
    }

    // Return the public parts
    return pwtImage;

}());

/*! choices.js v3.0.4 | (c) 2018 Josh Johnson | https://github.com/jshjohnson/Choices#readme */ 
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["Choices"] = factory();
	else
		root["Choices"] = factory();
})(this, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/assets/scripts/dist/";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	module.exports = __webpack_require__(1);


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _fuse = __webpack_require__(2);

	var _fuse2 = _interopRequireDefault(_fuse);

	var _classnames = __webpack_require__(3);

	var _classnames2 = _interopRequireDefault(_classnames);

	var _index = __webpack_require__(4);

	var _index2 = _interopRequireDefault(_index);

	var _index3 = __webpack_require__(31);

	var _utils = __webpack_require__(32);

	__webpack_require__(33);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	/**
	 * Choices
	 */
	var Choices = function () {
	  function Choices() {
	    var element = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '[data-choice]';
	    var userConfig = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	    _classCallCheck(this, Choices);

	    // If there are multiple elements, create a new instance
	    // for each element besides the first one (as that already has an instance)
	    if ((0, _utils.isType)('String', element)) {
	      var elements = document.querySelectorAll(element);
	      if (elements.length > 1) {
	        for (var i = 1; i < elements.length; i++) {
	          var el = elements[i];
	          new Choices(el, userConfig);
	        }
	      }
	    }

	    var defaultConfig = {
	      silent: false,
	      items: [],
	      choices: [],
	      renderChoiceLimit: -1,
	      maxItemCount: -1,
	      addItems: true,
	      removeItems: true,
	      removeItemButton: false,
	      editItems: false,
	      duplicateItems: true,
	      delimiter: ',',
	      paste: true,
	      searchEnabled: true,
	      searchChoices: true,
	      searchFloor: 1,
	      searchResultLimit: 4,
	      searchFields: ['label', 'value'],
	      position: 'auto',
	      resetScrollPosition: true,
	      regexFilter: null,
	      shouldSort: true,
	      shouldSortItems: false,
	      sortFilter: _utils.sortByAlpha,
	      placeholder: true,
	      placeholderValue: null,
	      searchPlaceholderValue: null,
	      prependValue: null,
	      appendValue: null,
	      renderSelectedChoices: 'auto',
	      loadingText: 'Loading...',
	      noResultsText: 'No results found',
	      noChoicesText: 'No choices to choose from',
	      itemSelectText: 'Press to select',
	      addItemText: function addItemText(value) {
	        return 'Press Enter to add <b>"' + (0, _utils.stripHTML)(value) + '"</b>';
	      },
	      maxItemText: function maxItemText(maxItemCount) {
	        return 'Only ' + maxItemCount + ' values can be added.';
	      },
	      itemComparer: function itemComparer(choice, item) {
	        return choice === item;
	      },
	      uniqueItemText: 'Only unique values can be added.',
	      classNames: {
	        containerOuter: 'choices',
	        containerInner: 'choices__inner',
	        input: 'choices__input',
	        inputCloned: 'choices__input--cloned',
	        list: 'choices__list',
	        listItems: 'choices__list--multiple',
	        listSingle: 'choices__list--single',
	        listDropdown: 'choices__list--dropdown',
	        item: 'choices__item',
	        itemSelectable: 'choices__item--selectable',
	        itemDisabled: 'choices__item--disabled',
	        itemChoice: 'choices__item--choice',
	        placeholder: 'choices__placeholder',
	        group: 'choices__group',
	        groupHeading: 'choices__heading',
	        button: 'choices__button',
	        activeState: 'is-active',
	        focusState: 'is-focused',
	        openState: 'is-open',
	        disabledState: 'is-disabled',
	        highlightedState: 'is-highlighted',
	        hiddenState: 'is-hidden',
	        flippedState: 'is-flipped',
	        loadingState: 'is-loading',
	        noResults: 'has-no-results',
	        noChoices: 'has-no-choices'
	      },
	      fuseOptions: {
	        include: 'score'
	      },
	      callbackOnInit: null,
	      callbackOnCreateTemplates: null
	    };

	    this.idNames = {
	      itemChoice: 'item-choice'
	    };

	    // Merge options with user options
	    this.config = (0, _utils.extend)(defaultConfig, userConfig);

	    if (this.config.renderSelectedChoices !== 'auto' && this.config.renderSelectedChoices !== 'always') {
	      if (!this.config.silent) {
	        console.warn('renderSelectedChoices: Possible values are \'auto\' and \'always\'. Falling back to \'auto\'.');
	      }
	      this.config.renderSelectedChoices = 'auto';
	    }

	    // Create data store
	    this.store = new _index2.default(this.render);

	    // State tracking
	    this.initialised = false;
	    this.currentState = {};
	    this.prevState = {};
	    this.currentValue = '';

	    // Retrieve triggering element (i.e. element with 'data-choice' trigger)
	    this.element = element;
	    this.passedElement = (0, _utils.isType)('String', element) ? document.querySelector(element) : element;

	    if (!this.passedElement) {
	      if (!this.config.silent) {
	        console.error('Passed element not found');
	      }
	      return;
	    }

	    this.isTextElement = this.passedElement.type === 'text';
	    this.isSelectOneElement = this.passedElement.type === 'select-one';
	    this.isSelectMultipleElement = this.passedElement.type === 'select-multiple';
	    this.isSelectElement = this.isSelectOneElement || this.isSelectMultipleElement;
	    this.isValidElementType = this.isTextElement || this.isSelectElement;
	    this.isIe11 = !!(navigator.userAgent.match(/Trident/) && navigator.userAgent.match(/rv[ :]11/));
	    this.isScrollingOnIe = false;

	    if (this.config.shouldSortItems === true && this.isSelectOneElement) {
	      if (!this.config.silent) {
	        console.warn('shouldSortElements: Type of passed element is \'select-one\', falling back to false.');
	      }
	    }

	    this.highlightPosition = 0;
	    this.canSearch = this.config.searchEnabled;

	    this.placeholder = false;
	    if (!this.isSelectOneElement) {
	      this.placeholder = this.config.placeholder ? this.config.placeholderValue || this.passedElement.getAttribute('placeholder') : false;
	    }

	    // Assign preset choices from passed object
	    this.presetChoices = this.config.choices;

	    // Assign preset items from passed object first
	    this.presetItems = this.config.items;

	    // Then add any values passed from attribute
	    if (this.passedElement.value) {
	      this.presetItems = this.presetItems.concat(this.passedElement.value.split(this.config.delimiter));
	    }

	    // Set unique base Id
	    this.baseId = (0, _utils.generateId)(this.passedElement, 'choices-');

	    // Bind methods
	    this.render = this.render.bind(this);

	    // Bind event handlers
	    this._onFocus = this._onFocus.bind(this);
	    this._onBlur = this._onBlur.bind(this);
	    this._onKeyUp = this._onKeyUp.bind(this);
	    this._onKeyDown = this._onKeyDown.bind(this);
	    this._onClick = this._onClick.bind(this);
	    this._onTouchMove = this._onTouchMove.bind(this);
	    this._onTouchEnd = this._onTouchEnd.bind(this);
	    this._onMouseDown = this._onMouseDown.bind(this);
	    this._onMouseOver = this._onMouseOver.bind(this);
	    this._onPaste = this._onPaste.bind(this);
	    this._onInput = this._onInput.bind(this);

	    // Monitor touch taps/scrolls
	    this.wasTap = true;

	    // Cutting the mustard
	    var cuttingTheMustard = 'classList' in document.documentElement;
	    if (!cuttingTheMustard && !this.config.silent) {
	      console.error('Choices: Your browser doesn\'t support Choices');
	    }

	    var canInit = (0, _utils.isElement)(this.passedElement) && this.isValidElementType;

	    if (canInit) {
	      // If element has already been initialised with Choices
	      if (this.passedElement.getAttribute('data-choice') === 'active') {
	        return;
	      }

	      // Let's go
	      this.init();
	    } else if (!this.config.silent) {
	      console.error('Incompatible input passed');
	    }
	  }

	  /*========================================
	  =            Public functions            =
	  ========================================*/

	  /**
	   * Initialise Choices
	   * @return
	   * @public
	   */


	  _createClass(Choices, [{
	    key: 'init',
	    value: function init() {
	      if (this.initialised === true) {
	        return;
	      }

	      var callback = this.config.callbackOnInit;

	      // Set initialise flag
	      this.initialised = true;
	      // Create required elements
	      this._createTemplates();
	      // Generate input markup
	      this._createInput();
	      // Subscribe store to render method
	      this.store.subscribe(this.render);
	      // Render any items
	      this.render();
	      // Trigger event listeners
	      this._addEventListeners();

	      // Run callback if it is a function
	      if (callback) {
	        if ((0, _utils.isType)('Function', callback)) {
	          callback.call(this);
	        }
	      }
	    }

	    /**
	     * Destroy Choices and nullify values
	     * @return
	     * @public
	     */

	  }, {
	    key: 'destroy',
	    value: function destroy() {
	      if (this.initialised === false) {
	        return;
	      }

	      // Remove all event listeners
	      this._removeEventListeners();

	      // Reinstate passed element
	      this.passedElement.classList.remove(this.config.classNames.input, this.config.classNames.hiddenState);
	      this.passedElement.removeAttribute('tabindex');
	      // Recover original styles if any
	      var origStyle = this.passedElement.getAttribute('data-choice-orig-style');
	      if (Boolean(origStyle)) {
	        this.passedElement.removeAttribute('data-choice-orig-style');
	        this.passedElement.setAttribute('style', origStyle);
	      } else {
	        this.passedElement.removeAttribute('style');
	      }
	      this.passedElement.removeAttribute('aria-hidden');
	      this.passedElement.removeAttribute('data-choice');

	      // Re-assign values - this is weird, I know
	      this.passedElement.value = this.passedElement.value;

	      // Move passed element back to original position
	      this.containerOuter.parentNode.insertBefore(this.passedElement, this.containerOuter);
	      // Remove added elements
	      this.containerOuter.parentNode.removeChild(this.containerOuter);

	      // Clear data store
	      this.clearStore();

	      // Nullify instance-specific data
	      this.config.templates = null;

	      // Uninitialise
	      this.initialised = false;
	    }

	    /**
	     * Render group choices into a DOM fragment and append to choice list
	     * @param  {Array} groups    Groups to add to list
	     * @param  {Array} choices   Choices to add to groups
	     * @param  {DocumentFragment} fragment Fragment to add groups and options to (optional)
	     * @return {DocumentFragment} Populated options fragment
	     * @private
	     */

	  }, {
	    key: 'renderGroups',
	    value: function renderGroups(groups, choices, fragment) {
	      var _this = this;

	      var groupFragment = fragment || document.createDocumentFragment();
	      var filter = this.config.sortFilter;

	      // If sorting is enabled, filter groups
	      if (this.config.shouldSort) {
	        groups.sort(filter);
	      }

	      groups.forEach(function (group) {
	        // Grab options that are children of this group
	        var groupChoices = choices.filter(function (choice) {
	          if (_this.isSelectOneElement) {
	            return choice.groupId === group.id;
	          }
	          return choice.groupId === group.id && !choice.selected;
	        });

	        if (groupChoices.length >= 1) {
	          var dropdownGroup = _this._getTemplate('choiceGroup', group);
	          groupFragment.appendChild(dropdownGroup);
	          _this.renderChoices(groupChoices, groupFragment, true);
	        }
	      });

	      return groupFragment;
	    }

	    /**
	     * Render choices into a DOM fragment and append to choice list
	     * @param  {Array} choices    Choices to add to list
	     * @param  {DocumentFragment} fragment Fragment to add choices to (optional)
	     * @return {DocumentFragment} Populated choices fragment
	     * @private
	     */

	  }, {
	    key: 'renderChoices',
	    value: function renderChoices(choices, fragment) {
	      var _this2 = this;

	      var withinGroup = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	      // Create a fragment to store our list items (so we don't have to update the DOM for each item)
	      var choicesFragment = fragment || document.createDocumentFragment();
	      var _config = this.config,
	          renderSelectedChoices = _config.renderSelectedChoices,
	          searchResultLimit = _config.searchResultLimit,
	          renderChoiceLimit = _config.renderChoiceLimit;

	      var filter = this.isSearching ? _utils.sortByScore : this.config.sortFilter;
	      var appendChoice = function appendChoice(choice) {
	        var shouldRender = renderSelectedChoices === 'auto' ? _this2.isSelectOneElement || !choice.selected : true;
	        if (shouldRender) {
	          var dropdownItem = _this2._getTemplate('choice', choice);
	          choicesFragment.appendChild(dropdownItem);
	        }
	      };

	      var rendererableChoices = choices;

	      if (renderSelectedChoices === 'auto' && !this.isSelectOneElement) {
	        rendererableChoices = choices.filter(function (choice) {
	          return !choice.selected;
	        });
	      }

	      // Split array into placeholders and "normal" choices

	      var _rendererableChoices$ = rendererableChoices.reduce(function (acc, choice) {
	        if (choice.placeholder) {
	          acc.placeholderChoices.push(choice);
	        } else {
	          acc.normalChoices.push(choice);
	        }
	        return acc;
	      }, { placeholderChoices: [], normalChoices: [] }),
	          placeholderChoices = _rendererableChoices$.placeholderChoices,
	          normalChoices = _rendererableChoices$.normalChoices;

	      // If sorting is enabled or the user is searching, filter choices


	      if (this.config.shouldSort || this.isSearching) {
	        normalChoices.sort(filter);
	      }

	      var choiceLimit = rendererableChoices.length;

	      // Prepend placeholeder
	      var sortedChoices = [].concat(_toConsumableArray(placeholderChoices), _toConsumableArray(normalChoices));

	      if (this.isSearching) {
	        choiceLimit = searchResultLimit;
	      } else if (renderChoiceLimit > 0 && !withinGroup) {
	        choiceLimit = renderChoiceLimit;
	      }

	      // Add each choice to dropdown within range
	      for (var i = 0; i < choiceLimit; i++) {
	        if (sortedChoices[i]) {
	          appendChoice(sortedChoices[i]);
	        }
	      };

	      return choicesFragment;
	    }

	    /**
	     * Render items into a DOM fragment and append to items list
	     * @param  {Array} items    Items to add to list
	     * @param  {DocumentFragment} [fragment] Fragment to add items to (optional)
	     * @return
	     * @private
	     */

	  }, {
	    key: 'renderItems',
	    value: function renderItems(items) {
	      var _this3 = this;

	      var fragment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      // Create fragment to add elements to
	      var itemListFragment = fragment || document.createDocumentFragment();

	      // If sorting is enabled, filter items
	      if (this.config.shouldSortItems && !this.isSelectOneElement) {
	        items.sort(this.config.sortFilter);
	      }

	      if (this.isTextElement) {
	        // Simplify store data to just values
	        var itemsFiltered = this.store.getItemsReducedToValues(items);
	        var itemsFilteredString = itemsFiltered.join(this.config.delimiter);
	        // Update the value of the hidden input
	        this.passedElement.setAttribute('value', itemsFilteredString);
	        this.passedElement.value = itemsFilteredString;
	      } else {
	        var selectedOptionsFragment = document.createDocumentFragment();

	        // Add each list item to list
	        items.forEach(function (item) {
	          // Create a standard select option
	          var option = _this3._getTemplate('option', item);
	          // Append it to fragment
	          selectedOptionsFragment.appendChild(option);
	        });

	        // Update selected choices
	        this.passedElement.innerHTML = '';
	        this.passedElement.appendChild(selectedOptionsFragment);
	      }

	      // Add each list item to list
	      items.forEach(function (item) {
	        // Create new list element
	        var listItem = _this3._getTemplate('item', item);
	        // Append it to list
	        itemListFragment.appendChild(listItem);
	      });

	      return itemListFragment;
	    }

	    /**
	     * Render DOM with values
	     * @return
	     * @private
	     */

	  }, {
	    key: 'render',
	    value: function render() {
	      if (this.store.isLoading()) {
	        return;
	      }

	      this.currentState = this.store.getState();

	      // Only render if our state has actually changed
	      if (this.currentState !== this.prevState) {
	        // Choices
	        if (this.currentState.choices !== this.prevState.choices || this.currentState.groups !== this.prevState.groups || this.currentState.items !== this.prevState.items) {
	          if (this.isSelectElement) {
	            // Get active groups/choices
	            var activeGroups = this.store.getGroupsFilteredByActive();
	            var activeChoices = this.store.getChoicesFilteredByActive();

	            var choiceListFragment = document.createDocumentFragment();

	            // Clear choices
	            this.choiceList.innerHTML = '';

	            // Scroll back to top of choices list
	            if (this.config.resetScrollPosition) {
	              this.choiceList.scrollTop = 0;
	            }

	            // If we have grouped options
	            if (activeGroups.length >= 1 && this.isSearching !== true) {
	              choiceListFragment = this.renderGroups(activeGroups, activeChoices, choiceListFragment);
	            } else if (activeChoices.length >= 1) {
	              choiceListFragment = this.renderChoices(activeChoices, choiceListFragment);
	            }

	            var activeItems = this.store.getItemsFilteredByActive();
	            var canAddItem = this._canAddItem(activeItems, this.input.value);

	            // If we have choices to show
	            if (choiceListFragment.childNodes && choiceListFragment.childNodes.length > 0) {
	              // ...and we can select them
	              if (canAddItem.response) {
	                // ...append them and highlight the first choice
	                this.choiceList.appendChild(choiceListFragment);
	                this._highlightChoice();
	              } else {
	                // ...otherwise show a notice
	                this.choiceList.appendChild(this._getTemplate('notice', canAddItem.notice));
	              }
	            } else {
	              // Otherwise show a notice
	              var dropdownItem = void 0;
	              var notice = void 0;

	              if (this.isSearching) {
	                notice = (0, _utils.isType)('Function', this.config.noResultsText) ? this.config.noResultsText() : this.config.noResultsText;

	                dropdownItem = this._getTemplate('notice', notice, 'no-results');
	              } else {
	                notice = (0, _utils.isType)('Function', this.config.noChoicesText) ? this.config.noChoicesText() : this.config.noChoicesText;

	                dropdownItem = this._getTemplate('notice', notice, 'no-choices');
	              }

	              this.choiceList.appendChild(dropdownItem);
	            }
	          }
	        }

	        // Items
	        if (this.currentState.items !== this.prevState.items) {
	          // Get active items (items that can be selected)
	          var _activeItems = this.store.getItemsFilteredByActive();

	          // Clear list
	          this.itemList.innerHTML = '';

	          if (_activeItems && _activeItems) {
	            // Create a fragment to store our list items
	            // (so we don't have to update the DOM for each item)
	            var itemListFragment = this.renderItems(_activeItems);

	            // If we have items to add
	            if (itemListFragment.childNodes) {
	              // Update list
	              this.itemList.appendChild(itemListFragment);
	            }
	          }
	        }

	        this.prevState = this.currentState;
	      }
	    }

	    /**
	     * Select item (a selected item can be deleted)
	     * @param  {Element} item Element to select
	     * @param  {Boolean} [runEvent=true] Whether to trigger 'highlightItem' event
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'highlightItem',
	    value: function highlightItem(item) {
	      var runEvent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	      if (!item) {
	        return this;
	      }

	      var id = item.id;
	      var groupId = item.groupId;
	      var group = groupId >= 0 ? this.store.getGroupById(groupId) : null;

	      this.store.dispatch((0, _index3.highlightItem)(id, true));

	      if (runEvent) {
	        if (group && group.value) {
	          (0, _utils.triggerEvent)(this.passedElement, 'highlightItem', {
	            id: id,
	            value: item.value,
	            label: item.label,
	            groupValue: group.value
	          });
	        } else {
	          (0, _utils.triggerEvent)(this.passedElement, 'highlightItem', {
	            id: id,
	            value: item.value,
	            label: item.label
	          });
	        }
	      }

	      return this;
	    }

	    /**
	     * Deselect item
	     * @param  {Element} item Element to de-select
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'unhighlightItem',
	    value: function unhighlightItem(item) {
	      if (!item) {
	        return this;
	      }

	      var id = item.id;
	      var groupId = item.groupId;
	      var group = groupId >= 0 ? this.store.getGroupById(groupId) : null;

	      this.store.dispatch((0, _index3.highlightItem)(id, false));

	      if (group && group.value) {
	        (0, _utils.triggerEvent)(this.passedElement, 'unhighlightItem', {
	          id: id,
	          value: item.value,
	          label: item.label,
	          groupValue: group.value
	        });
	      } else {
	        (0, _utils.triggerEvent)(this.passedElement, 'unhighlightItem', {
	          id: id,
	          value: item.value,
	          label: item.label
	        });
	      }

	      return this;
	    }

	    /**
	     * Highlight items within store
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'highlightAll',
	    value: function highlightAll() {
	      var _this4 = this;

	      var items = this.store.getItems();
	      items.forEach(function (item) {
	        _this4.highlightItem(item);
	      });

	      return this;
	    }

	    /**
	     * Deselect items within store
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'unhighlightAll',
	    value: function unhighlightAll() {
	      var _this5 = this;

	      var items = this.store.getItems();
	      items.forEach(function (item) {
	        _this5.unhighlightItem(item);
	      });

	      return this;
	    }

	    /**
	     * Remove an item from the store by its value
	     * @param  {String} value Value to search for
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'removeItemsByValue',
	    value: function removeItemsByValue(value) {
	      var _this6 = this;

	      if (!value || !(0, _utils.isType)('String', value)) {
	        return this;
	      }

	      var items = this.store.getItemsFilteredByActive();

	      items.forEach(function (item) {
	        if (item.value === value) {
	          _this6._removeItem(item);
	        }
	      });

	      return this;
	    }

	    /**
	     * Remove all items from store array
	     * @note Removed items are soft deleted
	     * @param  {Number} excludedId Optionally exclude item by ID
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'removeActiveItems',
	    value: function removeActiveItems(excludedId) {
	      var _this7 = this;

	      var items = this.store.getItemsFilteredByActive();

	      items.forEach(function (item) {
	        if (item.active && excludedId !== item.id) {
	          _this7._removeItem(item);
	        }
	      });

	      return this;
	    }

	    /**
	     * Remove all selected items from store
	     * @note Removed items are soft deleted
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'removeHighlightedItems',
	    value: function removeHighlightedItems() {
	      var _this8 = this;

	      var runEvent = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      var items = this.store.getItemsFilteredByActive();

	      items.forEach(function (item) {
	        if (item.highlighted && item.active) {
	          _this8._removeItem(item);
	          // If this action was performed by the user
	          // trigger the event
	          if (runEvent) {
	            _this8._triggerChange(item.value);
	          }
	        }
	      });

	      return this;
	    }

	    /**
	     * Show dropdown to user by adding active state class
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'showDropdown',
	    value: function showDropdown() {
	      var focusInput = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      var body = document.body;
	      var html = document.documentElement;
	      var winHeight = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);

	      this.containerOuter.classList.add(this.config.classNames.openState);
	      this.containerOuter.setAttribute('aria-expanded', 'true');
	      this.dropdown.classList.add(this.config.classNames.activeState);
	      this.dropdown.setAttribute('aria-expanded', 'true');

	      var dimensions = this.dropdown.getBoundingClientRect();
	      var dropdownPos = Math.ceil(dimensions.top + window.scrollY + this.dropdown.offsetHeight);

	      // If flip is enabled and the dropdown bottom position is greater than the window height flip the dropdown.
	      var shouldFlip = false;
	      if (this.config.position === 'auto') {
	        shouldFlip = dropdownPos >= winHeight;
	      } else if (this.config.position === 'top') {
	        shouldFlip = true;
	      }

	      if (shouldFlip) {
	        this.containerOuter.classList.add(this.config.classNames.flippedState);
	      }

	      // Optionally focus the input if we have a search input
	      if (focusInput && this.canSearch && document.activeElement !== this.input) {
	        this.input.focus();
	      }

	      (0, _utils.triggerEvent)(this.passedElement, 'showDropdown', {});

	      return this;
	    }

	    /**
	     * Hide dropdown from user
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'hideDropdown',
	    value: function hideDropdown() {
	      var blurInput = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      // A dropdown flips if it does not have space within the page
	      var isFlipped = this.containerOuter.classList.contains(this.config.classNames.flippedState);

	      this.containerOuter.classList.remove(this.config.classNames.openState);
	      this.containerOuter.setAttribute('aria-expanded', 'false');
	      this.dropdown.classList.remove(this.config.classNames.activeState);
	      this.dropdown.setAttribute('aria-expanded', 'false');

	      if (isFlipped) {
	        this.containerOuter.classList.remove(this.config.classNames.flippedState);
	      }

	      // Optionally blur the input if we have a search input
	      if (blurInput && this.canSearch && document.activeElement === this.input) {
	        this.input.blur();
	      }

	      (0, _utils.triggerEvent)(this.passedElement, 'hideDropdown', {});

	      return this;
	    }

	    /**
	     * Determine whether to hide or show dropdown based on its current state
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'toggleDropdown',
	    value: function toggleDropdown() {
	      var hasActiveDropdown = this.dropdown.classList.contains(this.config.classNames.activeState);
	      if (hasActiveDropdown) {
	        this.hideDropdown();
	      } else {
	        this.showDropdown(true);
	      }

	      return this;
	    }

	    /**
	     * Get value(s) of input (i.e. inputted items (text) or selected choices (select))
	     * @param {Boolean} valueOnly Get only values of selected items, otherwise return selected items
	     * @return {Array/String} selected value (select-one) or array of selected items (inputs & select-multiple)
	     * @public
	     */

	  }, {
	    key: 'getValue',
	    value: function getValue() {
	      var _this9 = this;

	      var valueOnly = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      var items = this.store.getItemsFilteredByActive();
	      var selectedItems = [];

	      items.forEach(function (item) {
	        if (_this9.isTextElement) {
	          selectedItems.push(valueOnly ? item.value : item);
	        } else if (item.active) {
	          selectedItems.push(valueOnly ? item.value : item);
	        }
	      });

	      if (this.isSelectOneElement) {
	        return selectedItems[0];
	      }

	      return selectedItems;
	    }

	    /**
	     * Set value of input. If the input is a select box, a choice will be created and selected otherwise
	     * an item will created directly.
	     * @param  {Array}   args  Array of value objects or value strings
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'setValue',
	    value: function setValue(args) {
	      var _this10 = this;

	      if (this.initialised === true) {
	        // Convert args to an iterable array
	        var values = [].concat(_toConsumableArray(args)),
	            handleValue = function handleValue(item) {
	          var itemType = (0, _utils.getType)(item);
	          if (itemType === 'Object') {
	            if (!item.value) {
	              return;
	            }

	            // If we are dealing with a select input, we need to create an option first
	            // that is then selected. For text inputs we can just add items normally.
	            if (!_this10.isTextElement) {
	              _this10._addChoice(item.value, item.label, true, false, -1, item.customProperties, item.placeholder);
	            } else {
	              _this10._addItem(item.value, item.label, item.id, undefined, item.customProperties, item.placeholder);
	            }
	          } else if (itemType === 'String') {
	            if (!_this10.isTextElement) {
	              _this10._addChoice(item, item, true, false, -1, null);
	            } else {
	              _this10._addItem(item);
	            }
	          }
	        };

	        if (values.length > 1) {
	          values.forEach(function (value) {
	            handleValue(value);
	          });
	        } else {
	          handleValue(values[0]);
	        }
	      }
	      return this;
	    }

	    /**
	     * Select value of select box via the value of an existing choice
	     * @param {Array/String} value An array of strings of a single string
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'setValueByChoice',
	    value: function setValueByChoice(value) {
	      var _this11 = this;

	      if (!this.isTextElement) {
	        var choices = this.store.getChoices();
	        // If only one value has been passed, convert to array
	        var choiceValue = (0, _utils.isType)('Array', value) ? value : [value];

	        // Loop through each value and
	        choiceValue.forEach(function (val) {
	          var foundChoice = choices.find(function (choice) {
	            // Check 'value' property exists and the choice isn't already selected
	            return _this11.config.itemComparer(choice.value, val);
	          });

	          if (foundChoice) {
	            if (!foundChoice.selected) {
	              _this11._addItem(foundChoice.value, foundChoice.label, foundChoice.id, foundChoice.groupId, foundChoice.customProperties, foundChoice.placeholder, foundChoice.keyCode);
	            } else if (!_this11.config.silent) {
	              console.warn('Attempting to select choice already selected');
	            }
	          } else if (!_this11.config.silent) {
	            console.warn('Attempting to select choice that does not exist');
	          }
	        });
	      }
	      return this;
	    }

	    /**
	     * Direct populate choices
	     * @param  {Array} choices - Choices to insert
	     * @param  {String} value - Name of 'value' property
	     * @param  {String} label - Name of 'label' property
	     * @param  {Boolean} replaceChoices Whether existing choices should be removed
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'setChoices',
	    value: function setChoices(choices, value, label) {
	      var _this12 = this;

	      var replaceChoices = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

	      if (this.initialised === true) {
	        if (this.isSelectElement) {
	          if (!(0, _utils.isType)('Array', choices) || !value) {
	            return this;
	          }

	          // Clear choices if needed
	          if (replaceChoices) {
	            this._clearChoices();
	          }

	          this._setLoading(true);

	          // Add choices if passed
	          if (choices && choices.length) {
	            this.containerOuter.classList.remove(this.config.classNames.loadingState);
	            choices.forEach(function (result) {
	              if (result.choices) {
	                _this12._addGroup(result, result.id || null, value, label);
	              } else {
	                _this12._addChoice(result[value], result[label], result.selected, result.disabled, undefined, result.customProperties, result.placeholder);
	              }
	            });
	          }

	          this._setLoading(false);
	        }
	      }
	      return this;
	    }

	    /**
	     * Clear items,choices and groups
	     * @note Hard delete
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'clearStore',
	    value: function clearStore() {
	      this.store.dispatch((0, _index3.clearAll)());
	      return this;
	    }

	    /**
	     * Set value of input to blank
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'clearInput',
	    value: function clearInput() {
	      if (this.input.value) {
	        this.input.value = '';
	      }
	      if (!this.isSelectOneElement) {
	        this._setInputWidth();
	      }
	      if (!this.isTextElement && this.config.searchEnabled) {
	        this.isSearching = false;
	        this.store.dispatch((0, _index3.activateChoices)(true));
	      }
	      return this;
	    }

	    /**
	     * Enable interaction with Choices
	     * @return {Object} Class instance
	     */

	  }, {
	    key: 'enable',
	    value: function enable() {
	      if (this.initialised) {
	        this.passedElement.disabled = false;
	        var isDisabled = this.containerOuter.classList.contains(this.config.classNames.disabledState);
	        if (isDisabled) {
	          this._addEventListeners();
	          this.passedElement.removeAttribute('disabled');
	          this.input.removeAttribute('disabled');
	          this.containerOuter.classList.remove(this.config.classNames.disabledState);
	          this.containerOuter.removeAttribute('aria-disabled');
	          if (this.isSelectOneElement) {
	            this.containerOuter.setAttribute('tabindex', '0');
	          }
	        }
	      }
	      return this;
	    }

	    /**
	     * Disable interaction with Choices
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'disable',
	    value: function disable() {
	      if (this.initialised) {
	        this.passedElement.disabled = true;
	        var isEnabled = !this.containerOuter.classList.contains(this.config.classNames.disabledState);
	        if (isEnabled) {
	          this._removeEventListeners();
	          this.passedElement.setAttribute('disabled', '');
	          this.input.setAttribute('disabled', '');
	          this.containerOuter.classList.add(this.config.classNames.disabledState);
	          this.containerOuter.setAttribute('aria-disabled', 'true');
	          if (this.isSelectOneElement) {
	            this.containerOuter.setAttribute('tabindex', '-1');
	          }
	        }
	      }
	      return this;
	    }

	    /**
	     * Populate options via ajax callback
	     * @param  {Function} fn Function that actually makes an AJAX request
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: 'ajax',
	    value: function ajax(fn) {
	      var _this13 = this;

	      if (this.initialised === true) {
	        if (this.isSelectElement) {
	          // Show loading text
	          requestAnimationFrame(function () {
	            _this13._handleLoadingState(true);
	          });
	          // Run callback
	          fn(this._ajaxCallback());
	        }
	      }
	      return this;
	    }

	    /*=====  End of Public functions  ======*/

	    /*=============================================
	    =                Private functions            =
	    =============================================*/

	    /**
	     * Call change callback
	     * @param  {String} value - last added/deleted/selected value
	     * @return
	     * @private
	     */

	  }, {
	    key: '_triggerChange',
	    value: function _triggerChange(value) {
	      if (!value) {
	        return;
	      }

	      (0, _utils.triggerEvent)(this.passedElement, 'change', {
	        value: value
	      });
	    }

	    /**
	     * Process enter/click of an item button
	     * @param {Array} activeItems The currently active items
	     * @param  {Element} element Button being interacted with
	     * @return
	     * @private
	     */

	  }, {
	    key: '_handleButtonAction',
	    value: function _handleButtonAction(activeItems, element) {
	      if (!activeItems || !element) {
	        return;
	      }

	      // If we are clicking on a button
	      if (this.config.removeItems && this.config.removeItemButton) {
	        var itemId = element.parentNode.getAttribute('data-id');
	        var itemToRemove = activeItems.find(function (item) {
	          return item.id === parseInt(itemId, 10);
	        });

	        // Remove item associated with button
	        this._removeItem(itemToRemove);
	        this._triggerChange(itemToRemove.value);

	        if (this.isSelectOneElement) {
	          this._selectPlaceholderChoice();
	        }
	      }
	    }

	    /**
	     * Select placeholder choice
	     */

	  }, {
	    key: '_selectPlaceholderChoice',
	    value: function _selectPlaceholderChoice() {
	      var placeholderChoice = this.store.getPlaceholderChoice();

	      if (placeholderChoice) {
	        this._addItem(placeholderChoice.value, placeholderChoice.label, placeholderChoice.id, placeholderChoice.groupId, null, placeholderChoice.placeholder);
	        this._triggerChange(placeholderChoice.value);
	      }
	    }

	    /**
	     * Process click of an item
	     * @param {Array} activeItems The currently active items
	     * @param  {Element} element Item being interacted with
	     * @param  {Boolean} hasShiftKey Whether the user has the shift key active
	     * @return
	     * @private
	     */

	  }, {
	    key: '_handleItemAction',
	    value: function _handleItemAction(activeItems, element) {
	      var _this14 = this;

	      var hasShiftKey = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	      if (!activeItems || !element) {
	        return;
	      }

	      // If we are clicking on an item
	      if (this.config.removeItems && !this.isSelectOneElement) {
	        var passedId = element.getAttribute('data-id');

	        // We only want to select one item with a click
	        // so we deselect any items that aren't the target
	        // unless shift is being pressed
	        activeItems.forEach(function (item) {
	          if (item.id === parseInt(passedId, 10) && !item.highlighted) {
	            _this14.highlightItem(item);
	          } else if (!hasShiftKey) {
	            if (item.highlighted) {
	              _this14.unhighlightItem(item);
	            }
	          }
	        });

	        // Focus input as without focus, a user cannot do anything with a
	        // highlighted item
	        if (document.activeElement !== this.input) {
	          this.input.focus();
	        }
	      }
	    }

	    /**
	     * Process click of a choice
	     * @param {Array} activeItems The currently active items
	     * @param  {Element} element Choice being interacted with
	     * @return
	     */

	  }, {
	    key: '_handleChoiceAction',
	    value: function _handleChoiceAction(activeItems, element) {
	      if (!activeItems || !element) {
	        return;
	      }

	      // If we are clicking on an option
	      var id = element.getAttribute('data-id');
	      var choice = this.store.getChoiceById(id);
	      var passedKeyCode = activeItems[0] && activeItems[0].keyCode ? activeItems[0].keyCode : null;
	      var hasActiveDropdown = this.dropdown.classList.contains(this.config.classNames.activeState);

	      // Update choice keyCode
	      choice.keyCode = passedKeyCode;

	      (0, _utils.triggerEvent)(this.passedElement, 'choice', {
	        choice: choice
	      });

	      if (choice && !choice.selected && !choice.disabled) {
	        var canAddItem = this._canAddItem(activeItems, choice.value);

	        if (canAddItem.response) {
	          this._addItem(choice.value, choice.label, choice.id, choice.groupId, choice.customProperties, choice.placeholder, choice.keyCode);
	          this._triggerChange(choice.value);
	        }
	      }

	      this.clearInput();

	      // We wont to close the dropdown if we are dealing with a single select box
	      if (hasActiveDropdown && this.isSelectOneElement) {
	        this.hideDropdown();
	        this.containerOuter.focus();
	      }
	    }

	    /**
	     * Process back space event
	     * @param  {Array} activeItems items
	     * @return
	     * @private
	     */

	  }, {
	    key: '_handleBackspace',
	    value: function _handleBackspace(activeItems) {
	      if (this.config.removeItems && activeItems) {
	        var lastItem = activeItems[activeItems.length - 1];
	        var hasHighlightedItems = activeItems.some(function (item) {
	          return item.highlighted;
	        });

	        // If editing the last item is allowed and there are not other selected items,
	        // we can edit the item value. Otherwise if we can remove items, remove all selected items
	        if (this.config.editItems && !hasHighlightedItems && lastItem) {
	          this.input.value = lastItem.value;
	          this._setInputWidth();
	          this._removeItem(lastItem);
	          this._triggerChange(lastItem.value);
	        } else {
	          if (!hasHighlightedItems) {
	            this.highlightItem(lastItem, false);
	          }
	          this.removeHighlightedItems(true);
	        }
	      }
	    }

	    /**
	     * Validates whether an item can be added by a user
	     * @param {Array} activeItems The currently active items
	     * @param  {String} value     Value of item to add
	     * @return {Object}           Response: Whether user can add item
	     *                            Notice: Notice show in dropdown
	     */

	  }, {
	    key: '_canAddItem',
	    value: function _canAddItem(activeItems, value) {
	      var canAddItem = true;
	      var notice = (0, _utils.isType)('Function', this.config.addItemText) ? this.config.addItemText(value) : this.config.addItemText;

	      if (this.isSelectMultipleElement || this.isTextElement) {
	        if (this.config.maxItemCount > 0 && this.config.maxItemCount <= activeItems.length) {
	          // If there is a max entry limit and we have reached that limit
	          // don't update
	          canAddItem = false;
	          notice = (0, _utils.isType)('Function', this.config.maxItemText) ? this.config.maxItemText(this.config.maxItemCount) : this.config.maxItemText;
	        }
	      }

	      if (this.isTextElement && this.config.addItems && canAddItem) {
	        // If a user has supplied a regular expression filter
	        if (this.config.regexFilter) {
	          // Determine whether we can update based on whether
	          // our regular expression passes
	          canAddItem = this._regexFilter(value);
	        }
	      }

	      // If no duplicates are allowed, and the value already exists
	      // in the array
	      var isUnique = !activeItems.some(function (item) {
	        if ((0, _utils.isType)('String', value)) {
	          return item.value === value.trim();
	        }

	        return item.value === value;
	      });

	      if (!isUnique && !this.config.duplicateItems && !this.isSelectOneElement && canAddItem) {
	        canAddItem = false;
	        notice = (0, _utils.isType)('Function', this.config.uniqueItemText) ? this.config.uniqueItemText(value) : this.config.uniqueItemText;
	      }

	      return {
	        response: canAddItem,
	        notice: notice
	      };
	    }

	    /**
	     * Apply or remove a loading state to the component.
	     * @param {Boolean} setLoading default value set to 'true'.
	     * @return
	     * @private
	     */

	  }, {
	    key: '_handleLoadingState',
	    value: function _handleLoadingState() {
	      var setLoading = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	      var placeholderItem = this.itemList.querySelector('.' + this.config.classNames.placeholder);
	      if (setLoading) {
	        this.containerOuter.classList.add(this.config.classNames.loadingState);
	        this.containerOuter.setAttribute('aria-busy', 'true');
	        if (this.isSelectOneElement) {
	          if (!placeholderItem) {
	            placeholderItem = this._getTemplate('placeholder', this.config.loadingText);
	            this.itemList.appendChild(placeholderItem);
	          } else {
	            placeholderItem.innerHTML = this.config.loadingText;
	          }
	        } else {
	          this.input.placeholder = this.config.loadingText;
	        }
	      } else {
	        // Remove loading states/text
	        this.containerOuter.classList.remove(this.config.classNames.loadingState);

	        if (this.isSelectOneElement) {
	          placeholderItem.innerHTML = this.placeholder || '';
	        } else {
	          this.input.placeholder = this.placeholder || '';
	        }
	      }
	    }

	    /**
	     * Retrieve the callback used to populate component's choices in an async way.
	     * @returns {Function} The callback as a function.
	     * @private
	     */

	  }, {
	    key: '_ajaxCallback',
	    value: function _ajaxCallback() {
	      var _this15 = this;

	      return function (results, value, label) {
	        if (!results || !value) {
	          return;
	        }

	        var parsedResults = (0, _utils.isType)('Object', results) ? [results] : results;

	        if (parsedResults && (0, _utils.isType)('Array', parsedResults) && parsedResults.length) {
	          // Remove loading states/text
	          _this15._handleLoadingState(false);
	          // Add each result as a choice

	          _this15._setLoading(true);

	          parsedResults.forEach(function (result) {
	            if (result.choices) {
	              var groupId = result.id || null;
	              _this15._addGroup(result, groupId, value, label);
	            } else {
	              _this15._addChoice(result[value], result[label], result.selected, result.disabled, undefined, result.customProperties, result.placeholder);
	            }
	          });

	          _this15._setLoading(false);

	          if (_this15.isSelectOneElement) {
	            _this15._selectPlaceholderChoice();
	          }
	        } else {
	          // No results, remove loading state
	          _this15._handleLoadingState(false);
	        }

	        _this15.containerOuter.removeAttribute('aria-busy');
	      };
	    }

	    /**
	     * Filter choices based on search value
	     * @param  {String} value Value to filter by
	     * @return
	     * @private
	     */

	  }, {
	    key: '_searchChoices',
	    value: function _searchChoices(value) {
	      var newValue = (0, _utils.isType)('String', value) ? value.trim() : value;
	      var currentValue = (0, _utils.isType)('String', this.currentValue) ? this.currentValue.trim() : this.currentValue;

	      // If new value matches the desired length and is not the same as the current value with a space
	      if (newValue.length >= 1 && newValue !== currentValue + ' ') {
	        var haystack = this.store.getSearchableChoices();
	        var needle = newValue;
	        var keys = (0, _utils.isType)('Array', this.config.searchFields) ? this.config.searchFields : [this.config.searchFields];
	        var options = Object.assign(this.config.fuseOptions, { keys: keys });
	        var fuse = new _fuse2.default(haystack, options);
	        var results = fuse.search(needle);

	        this.currentValue = newValue;
	        this.highlightPosition = 0;
	        this.isSearching = true;
	        this.store.dispatch((0, _index3.filterChoices)(results));

	        return results.length;
	      }

	      return 0;
	    }

	    /**
	     * Determine the action when a user is searching
	     * @param  {String} value Value entered by user
	     * @return
	     * @private
	     */

	  }, {
	    key: '_handleSearch',
	    value: function _handleSearch(value) {
	      if (!value) {
	        return;
	      }

	      var choices = this.store.getChoices();
	      var hasUnactiveChoices = choices.some(function (option) {
	        return !option.active;
	      });

	      // Run callback if it is a function
	      if (this.input === document.activeElement) {
	        // Check that we have a value to search and the input was an alphanumeric character
	        if (value && value.length >= this.config.searchFloor) {
	          var resultCount = 0;
	          // Check flag to filter search input
	          if (this.config.searchChoices) {
	            // Filter available choices
	            resultCount = this._searchChoices(value);
	          }
	          // Trigger search event
	          (0, _utils.triggerEvent)(this.passedElement, 'search', {
	            value: value,
	            resultCount: resultCount
	          });
	        } else if (hasUnactiveChoices) {
	          // Otherwise reset choices to active
	          this.isSearching = false;
	          this.store.dispatch((0, _index3.activateChoices)(true));
	        }
	      }
	    }

	    /**
	     * Trigger event listeners
	     * @return
	     * @private
	     */

	  }, {
	    key: '_addEventListeners',
	    value: function _addEventListeners() {
	      document.addEventListener('keyup', this._onKeyUp);
	      document.addEventListener('keydown', this._onKeyDown);
	      document.addEventListener('click', this._onClick);
	      document.addEventListener('touchmove', this._onTouchMove);
	      document.addEventListener('touchend', this._onTouchEnd);
	      document.addEventListener('mousedown', this._onMouseDown);
	      document.addEventListener('mouseover', this._onMouseOver);

	      if (this.isSelectOneElement) {
	        this.containerOuter.addEventListener('focus', this._onFocus);
	        this.containerOuter.addEventListener('blur', this._onBlur);
	      }

	      this.input.addEventListener('input', this._onInput);
	      this.input.addEventListener('paste', this._onPaste);
	      this.input.addEventListener('focus', this._onFocus);
	      this.input.addEventListener('blur', this._onBlur);
	    }

	    /**
	     * Remove event listeners
	     * @return
	     * @private
	     */

	  }, {
	    key: '_removeEventListeners',
	    value: function _removeEventListeners() {
	      document.removeEventListener('keyup', this._onKeyUp);
	      document.removeEventListener('keydown', this._onKeyDown);
	      document.removeEventListener('click', this._onClick);
	      document.removeEventListener('touchmove', this._onTouchMove);
	      document.removeEventListener('touchend', this._onTouchEnd);
	      document.removeEventListener('mousedown', this._onMouseDown);
	      document.removeEventListener('mouseover', this._onMouseOver);

	      if (this.isSelectOneElement) {
	        this.containerOuter.removeEventListener('focus', this._onFocus);
	        this.containerOuter.removeEventListener('blur', this._onBlur);
	      }

	      this.input.removeEventListener('input', this._onInput);
	      this.input.removeEventListener('paste', this._onPaste);
	      this.input.removeEventListener('focus', this._onFocus);
	      this.input.removeEventListener('blur', this._onBlur);
	    }

	    /**
	     * Set the correct input width based on placeholder
	     * value or input value
	     * @return
	     */

	  }, {
	    key: '_setInputWidth',
	    value: function _setInputWidth() {
	      if (this.placeholder) {
	        // If there is a placeholder, we only want to set the width of the input when it is a greater
	        // length than 75% of the placeholder. This stops the input jumping around.
	        if (this.input.value && this.input.value.length >= this.placeholder.length / 1.25) {
	          this.input.style.width = (0, _utils.getWidthOfInput)(this.input);
	        }
	      } else {
	        // If there is no placeholder, resize input to contents
	        this.input.style.width = (0, _utils.getWidthOfInput)(this.input);
	      }
	    }

	    /**
	     * Key down event
	     * @param  {Object} e Event
	     * @return
	     */

	  }, {
	    key: '_onKeyDown',
	    value: function _onKeyDown(e) {
	      var _this16 = this,
	          _keyDownActions;

	      if (e.target !== this.input && !this.containerOuter.contains(e.target)) {
	        return;
	      }

	      var target = e.target;
	      var activeItems = this.store.getItemsFilteredByActive();
	      var hasFocusedInput = this.input === document.activeElement;
	      var hasActiveDropdown = this.dropdown.classList.contains(this.config.classNames.activeState);
	      var hasItems = this.itemList && this.itemList.children;
	      var keyString = String.fromCharCode(e.keyCode);

	      var backKey = 46;
	      var deleteKey = 8;
	      var enterKey = 13;
	      var aKey = 65;
	      var escapeKey = 27;
	      var upKey = 38;
	      var downKey = 40;
	      var pageUpKey = 33;
	      var pageDownKey = 34;
	      var ctrlDownKey = e.ctrlKey || e.metaKey;

	      // If a user is typing and the dropdown is not active
	      if (!this.isTextElement && /[a-zA-Z0-9-_ ]/.test(keyString) && !hasActiveDropdown) {
	        this.showDropdown(true);
	      }

	      this.canSearch = this.config.searchEnabled;

	      var onAKey = function onAKey() {
	        // If CTRL + A or CMD + A have been pressed and there are items to select
	        if (ctrlDownKey && hasItems) {
	          _this16.canSearch = false;
	          if (_this16.config.removeItems && !_this16.input.value && _this16.input === document.activeElement) {
	            // Highlight items
	            _this16.highlightAll();
	          }
	        }
	      };

	      var onEnterKey = function onEnterKey() {
	        // If enter key is pressed and the input has a value
	        if (_this16.isTextElement && target.value) {
	          var value = _this16.input.value;
	          var canAddItem = _this16._canAddItem(activeItems, value);

	          // All is good, add
	          if (canAddItem.response) {
	            if (hasActiveDropdown) {
	              _this16.hideDropdown();
	            }
	            _this16._addItem(value);
	            _this16._triggerChange(value);
	            _this16.clearInput();
	          }
	        }

	        if (target.hasAttribute('data-button')) {
	          _this16._handleButtonAction(activeItems, target);
	          e.preventDefault();
	        }

	        if (hasActiveDropdown) {
	          e.preventDefault();
	          var highlighted = _this16.dropdown.querySelector('.' + _this16.config.classNames.highlightedState);

	          // If we have a highlighted choice
	          if (highlighted) {
	            // add enter keyCode value
	            if (activeItems[0]) {
	              activeItems[0].keyCode = enterKey;
	            }
	            _this16._handleChoiceAction(activeItems, highlighted);
	          }
	        } else if (_this16.isSelectOneElement) {
	          // Open single select dropdown if it's not active
	          if (!hasActiveDropdown) {
	            _this16.showDropdown(true);
	            e.preventDefault();
	          }
	        }
	      };

	      var onEscapeKey = function onEscapeKey() {
	        if (hasActiveDropdown) {
	          _this16.toggleDropdown();
	          _this16.containerOuter.focus();
	        }
	      };

	      var onDirectionKey = function onDirectionKey() {
	        // If up or down key is pressed, traverse through options
	        if (hasActiveDropdown || _this16.isSelectOneElement) {
	          // Show dropdown if focus
	          if (!hasActiveDropdown) {
	            _this16.showDropdown(true);
	          }

	          _this16.canSearch = false;

	          var directionInt = e.keyCode === downKey || e.keyCode === pageDownKey ? 1 : -1;
	          var skipKey = e.metaKey || e.keyCode === pageDownKey || e.keyCode === pageUpKey;

	          var nextEl = void 0;
	          if (skipKey) {
	            if (directionInt > 0) {
	              nextEl = Array.from(_this16.dropdown.querySelectorAll('[data-choice-selectable]')).pop();
	            } else {
	              nextEl = _this16.dropdown.querySelector('[data-choice-selectable]');
	            }
	          } else {
	            var currentEl = _this16.dropdown.querySelector('.' + _this16.config.classNames.highlightedState);
	            if (currentEl) {
	              nextEl = (0, _utils.getAdjacentEl)(currentEl, '[data-choice-selectable]', directionInt);
	            } else {
	              nextEl = _this16.dropdown.querySelector('[data-choice-selectable]');
	            }
	          }

	          if (nextEl) {
	            // We prevent default to stop the cursor moving
	            // when pressing the arrow
	            if (!(0, _utils.isScrolledIntoView)(nextEl, _this16.choiceList, directionInt)) {
	              _this16._scrollToChoice(nextEl, directionInt);
	            }
	            _this16._highlightChoice(nextEl);
	          }

	          // Prevent default to maintain cursor position whilst
	          // traversing dropdown options
	          e.preventDefault();
	        }
	      };

	      var onDeleteKey = function onDeleteKey() {
	        // If backspace or delete key is pressed and the input has no value
	        if (hasFocusedInput && !e.target.value && !_this16.isSelectOneElement) {
	          _this16._handleBackspace(activeItems);
	          e.preventDefault();
	        }
	      };

	      // Map keys to key actions
	      var keyDownActions = (_keyDownActions = {}, _defineProperty(_keyDownActions, aKey, onAKey), _defineProperty(_keyDownActions, enterKey, onEnterKey), _defineProperty(_keyDownActions, escapeKey, onEscapeKey), _defineProperty(_keyDownActions, upKey, onDirectionKey), _defineProperty(_keyDownActions, pageUpKey, onDirectionKey), _defineProperty(_keyDownActions, downKey, onDirectionKey), _defineProperty(_keyDownActions, pageDownKey, onDirectionKey), _defineProperty(_keyDownActions, deleteKey, onDeleteKey), _defineProperty(_keyDownActions, backKey, onDeleteKey), _keyDownActions);

	      // If keycode has a function, run it
	      if (keyDownActions[e.keyCode]) {
	        keyDownActions[e.keyCode]();
	      }
	    }

	    /**
	     * Key up event
	     * @param  {Object} e Event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onKeyUp',
	    value: function _onKeyUp(e) {
	      if (e.target !== this.input) {
	        return;
	      }

	      var value = this.input.value;
	      var activeItems = this.store.getItemsFilteredByActive();
	      var canAddItem = this._canAddItem(activeItems, value);

	      // We are typing into a text input and have a value, we want to show a dropdown
	      // notice. Otherwise hide the dropdown
	      if (this.isTextElement) {
	        var hasActiveDropdown = this.dropdown.classList.contains(this.config.classNames.activeState);
	        if (value) {

	          if (canAddItem.notice) {
	            var dropdownItem = this._getTemplate('notice', canAddItem.notice);
	            this.dropdown.innerHTML = dropdownItem.outerHTML;
	          }

	          if (canAddItem.response === true) {
	            if (!hasActiveDropdown) {
	              this.showDropdown();
	            }
	          } else if (!canAddItem.notice && hasActiveDropdown) {
	            this.hideDropdown();
	          }
	        } else if (hasActiveDropdown) {
	          this.hideDropdown();
	        }
	      } else {
	        var backKey = 46;
	        var deleteKey = 8;

	        // If user has removed value...
	        if ((e.keyCode === backKey || e.keyCode === deleteKey) && !e.target.value) {
	          // ...and it is a multiple select input, activate choices (if searching)
	          if (!this.isTextElement && this.isSearching) {
	            this.isSearching = false;
	            this.store.dispatch((0, _index3.activateChoices)(true));
	          }
	        } else if (this.canSearch && canAddItem.response) {
	          this._handleSearch(this.input.value);
	        }
	      }
	      // Re-establish canSearch value from changes in _onKeyDown
	      this.canSearch = this.config.searchEnabled;
	    }

	    /**
	     * Input event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onInput',
	    value: function _onInput() {
	      if (!this.isSelectOneElement) {
	        this._setInputWidth();
	      }
	    }

	    /**
	     * Touch move event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onTouchMove',
	    value: function _onTouchMove() {
	      if (this.wasTap === true) {
	        this.wasTap = false;
	      }
	    }

	    /**
	     * Touch end event
	     * @param  {Object} e Event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onTouchEnd',
	    value: function _onTouchEnd(e) {
	      var target = e.target || e.touches[0].target;
	      var hasActiveDropdown = this.dropdown.classList.contains(this.config.classNames.activeState);

	      // If a user tapped within our container...
	      if (this.wasTap === true && this.containerOuter.contains(target)) {
	        // ...and we aren't dealing with a single select box, show dropdown/focus input
	        if ((target === this.containerOuter || target === this.containerInner) && !this.isSelectOneElement) {
	          if (this.isTextElement) {
	            // If text element, we only want to focus the input (if it isn't already)
	            if (document.activeElement !== this.input) {
	              this.input.focus();
	            }
	          } else {
	            if (!hasActiveDropdown) {
	              // If a select box, we want to show the dropdown
	              this.showDropdown(true);
	            }
	          }
	        }
	        // Prevents focus event firing
	        e.stopPropagation();
	      }

	      this.wasTap = true;
	    }

	    /**
	     * Mouse down event
	     * @param  {Object} e Event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onMouseDown',
	    value: function _onMouseDown(e) {
	      var target = e.target;

	      // If we have our mouse down on the scrollbar and are on IE11...
	      if (target === this.choiceList && this.isIe11) {
	        this.isScrollingOnIe = true;
	      }

	      if (this.containerOuter.contains(target) && target !== this.input) {
	        var foundTarget = void 0;
	        var activeItems = this.store.getItemsFilteredByActive();
	        var hasShiftKey = e.shiftKey;

	        if (foundTarget = (0, _utils.findAncestorByAttrName)(target, 'data-button')) {
	          this._handleButtonAction(activeItems, foundTarget);
	        } else if (foundTarget = (0, _utils.findAncestorByAttrName)(target, 'data-item')) {
	          this._handleItemAction(activeItems, foundTarget, hasShiftKey);
	        } else if (foundTarget = (0, _utils.findAncestorByAttrName)(target, 'data-choice')) {
	          this._handleChoiceAction(activeItems, foundTarget);
	        }

	        e.preventDefault();
	      }
	    }

	    /**
	     * Click event
	     * @param  {Object} e Event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onClick',
	    value: function _onClick(e) {
	      var target = e.target;
	      var hasActiveDropdown = this.dropdown.classList.contains(this.config.classNames.activeState);
	      var activeItems = this.store.getItemsFilteredByActive();

	      // If target is something that concerns us
	      if (this.containerOuter.contains(target)) {
	        // Handle button delete
	        if (target.hasAttribute('data-button')) {
	          this._handleButtonAction(activeItems, target);
	        }

	        if (!hasActiveDropdown) {
	          if (this.isTextElement) {
	            if (document.activeElement !== this.input) {
	              this.input.focus();
	            }
	          } else {
	            if (this.canSearch) {
	              this.showDropdown(true);
	            } else {
	              this.showDropdown();
	              this.containerOuter.focus();
	            }
	          }
	        } else if (this.isSelectOneElement && target !== this.input && !this.dropdown.contains(target)) {
	          this.hideDropdown(true);
	        }
	      } else {
	        var hasHighlightedItems = activeItems.some(function (item) {
	          return item.highlighted;
	        });

	        // De-select any highlighted items
	        if (hasHighlightedItems) {
	          this.unhighlightAll();
	        }

	        // Remove focus state
	        this.containerOuter.classList.remove(this.config.classNames.focusState);

	        // Close all other dropdowns
	        if (hasActiveDropdown) {
	          this.hideDropdown();
	        }
	      }
	    }

	    /**
	     * Mouse over (hover) event
	     * @param  {Object} e Event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onMouseOver',
	    value: function _onMouseOver(e) {
	      // If the dropdown is either the target or one of its children is the target
	      if (e.target === this.dropdown || this.dropdown.contains(e.target)) {
	        if (e.target.hasAttribute('data-choice')) this._highlightChoice(e.target);
	      }
	    }

	    /**
	     * Paste event
	     * @param  {Object} e Event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onPaste',
	    value: function _onPaste(e) {
	      // Disable pasting into the input if option has been set
	      if (e.target === this.input && !this.config.paste) {
	        e.preventDefault();
	      }
	    }

	    /**
	     * Focus event
	     * @param  {Object} e Event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onFocus',
	    value: function _onFocus(e) {
	      var _this17 = this;

	      var target = e.target;
	      // If target is something that concerns us
	      if (this.containerOuter.contains(target)) {
	        var hasActiveDropdown = this.dropdown.classList.contains(this.config.classNames.activeState);
	        var focusActions = {
	          text: function text() {
	            if (target === _this17.input) {
	              _this17.containerOuter.classList.add(_this17.config.classNames.focusState);
	            }
	          },
	          'select-one': function selectOne() {
	            _this17.containerOuter.classList.add(_this17.config.classNames.focusState);
	            if (target === _this17.input) {
	              // Show dropdown if it isn't already showing
	              if (!hasActiveDropdown) {
	                _this17.showDropdown();
	              }
	            }
	          },
	          'select-multiple': function selectMultiple() {
	            if (target === _this17.input) {
	              // If element is a select box, the focused element is the container and the dropdown
	              // isn't already open, focus and show dropdown
	              _this17.containerOuter.classList.add(_this17.config.classNames.focusState);

	              if (!hasActiveDropdown) {
	                _this17.showDropdown(true);
	              }
	            }
	          }
	        };

	        focusActions[this.passedElement.type]();
	      }
	    }

	    /**
	     * Blur event
	     * @param  {Object} e Event
	     * @return
	     * @private
	     */

	  }, {
	    key: '_onBlur',
	    value: function _onBlur(e) {
	      var _this18 = this;

	      var target = e.target;
	      // If target is something that concerns us
	      if (this.containerOuter.contains(target) && !this.isScrollingOnIe) {
	        var activeItems = this.store.getItemsFilteredByActive();
	        var hasActiveDropdown = this.dropdown.classList.contains(this.config.classNames.activeState);
	        var hasHighlightedItems = activeItems.some(function (item) {
	          return item.highlighted;
	        });
	        var blurActions = {
	          text: function text() {
	            if (target === _this18.input) {
	              // Remove the focus state
	              _this18.containerOuter.classList.remove(_this18.config.classNames.focusState);
	              // De-select any highlighted items
	              if (hasHighlightedItems) {
	                _this18.unhighlightAll();
	              }
	              // Hide dropdown if it is showing
	              if (hasActiveDropdown) {
	                _this18.hideDropdown();
	              }
	            }
	          },
	          'select-one': function selectOne() {
	            _this18.containerOuter.classList.remove(_this18.config.classNames.focusState);
	            if (target === _this18.containerOuter) {
	              // Hide dropdown if it is showing
	              if (hasActiveDropdown && !_this18.canSearch) {
	                _this18.hideDropdown();
	              }
	            }
	            if (target === _this18.input && hasActiveDropdown) {
	              // Hide dropdown if it is showing
	              _this18.hideDropdown();
	            }
	          },
	          'select-multiple': function selectMultiple() {
	            if (target === _this18.input) {
	              // Remove the focus state
	              _this18.containerOuter.classList.remove(_this18.config.classNames.focusState);
	              // Hide dropdown if it is showing
	              if (hasActiveDropdown) {
	                _this18.hideDropdown();
	              }
	              // De-select any highlighted items
	              if (hasHighlightedItems) {
	                _this18.unhighlightAll();
	              }
	            }
	          }
	        };

	        blurActions[this.passedElement.type]();
	      } else {
	        // On IE11, clicking the scollbar blurs our input and thus
	        // closes the dropdown. To stop this, we refocus our input
	        // if we know we are on IE *and* are scrolling.
	        this.isScrollingOnIe = false;
	        this.input.focus();
	      }
	    }

	    /**
	     * Tests value against a regular expression
	     * @param  {string} value   Value to test
	     * @return {Boolean}        Whether test passed/failed
	     * @private
	     */

	  }, {
	    key: '_regexFilter',
	    value: function _regexFilter(value) {
	      if (!value) {
	        return false;
	      }

	      var regex = this.config.regexFilter;
	      var expression = new RegExp(regex.source, 'i');
	      return expression.test(value);
	    }

	    /**
	     * Scroll to an option element
	     * @param  {HTMLElement} choice  Option to scroll to
	     * @param  {Number} direction  Whether option is above or below
	     * @return
	     * @private
	     */

	  }, {
	    key: '_scrollToChoice',
	    value: function _scrollToChoice(choice, direction) {
	      var _this19 = this;

	      if (!choice) {
	        return;
	      }

	      var dropdownHeight = this.choiceList.offsetHeight;
	      var choiceHeight = choice.offsetHeight;
	      // Distance from bottom of element to top of parent
	      var choicePos = choice.offsetTop + choiceHeight;
	      // Scroll position of dropdown
	      var containerScrollPos = this.choiceList.scrollTop + dropdownHeight;
	      // Difference between the choice and scroll position
	      var endPoint = direction > 0 ? this.choiceList.scrollTop + choicePos - containerScrollPos : choice.offsetTop;

	      var animateScroll = function animateScroll() {
	        var strength = 4;
	        var choiceListScrollTop = _this19.choiceList.scrollTop;
	        var continueAnimation = false;
	        var easing = void 0;
	        var distance = void 0;

	        if (direction > 0) {
	          easing = (endPoint - choiceListScrollTop) / strength;
	          distance = easing > 1 ? easing : 1;

	          _this19.choiceList.scrollTop = choiceListScrollTop + distance;
	          if (choiceListScrollTop < endPoint) {
	            continueAnimation = true;
	          }
	        } else {
	          easing = (choiceListScrollTop - endPoint) / strength;
	          distance = easing > 1 ? easing : 1;

	          _this19.choiceList.scrollTop = choiceListScrollTop - distance;
	          if (choiceListScrollTop > endPoint) {
	            continueAnimation = true;
	          }
	        }

	        if (continueAnimation) {
	          requestAnimationFrame(function (time) {
	            animateScroll(time, endPoint, direction);
	          });
	        }
	      };

	      requestAnimationFrame(function (time) {
	        animateScroll(time, endPoint, direction);
	      });
	    }

	    /**
	     * Highlight choice
	     * @param  {HTMLElement} [el] Element to highlight
	     * @return
	     * @private
	     */

	  }, {
	    key: '_highlightChoice',
	    value: function _highlightChoice() {
	      var _this20 = this;

	      var el = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      // Highlight first element in dropdown
	      var choices = Array.from(this.dropdown.querySelectorAll('[data-choice-selectable]'));
	      var passedEl = el;

	      if (choices && choices.length) {
	        var highlightedChoices = Array.from(this.dropdown.querySelectorAll('.' + this.config.classNames.highlightedState));

	        // Remove any highlighted choices
	        highlightedChoices.forEach(function (choice) {
	          choice.classList.remove(_this20.config.classNames.highlightedState);
	          choice.setAttribute('aria-selected', 'false');
	        });

	        if (passedEl) {
	          this.highlightPosition = choices.indexOf(passedEl);
	        } else {
	          // Highlight choice based on last known highlight location
	          if (choices.length > this.highlightPosition) {
	            // If we have an option to highlight
	            passedEl = choices[this.highlightPosition];
	          } else {
	            // Otherwise highlight the option before
	            passedEl = choices[choices.length - 1];
	          }

	          if (!passedEl) {
	            passedEl = choices[0];
	          }
	        }

	        // Highlight given option, and set accessiblity attributes
	        passedEl.classList.add(this.config.classNames.highlightedState);
	        passedEl.setAttribute('aria-selected', 'true');
	        this.containerOuter.setAttribute('aria-activedescendant', passedEl.id);
	      }
	    }

	    /**
	     * Add item to store with correct value
	     * @param {String} value Value to add to store
	     * @param {String} [label] Label to add to store
	     * @param {Number} [choiceId=-1] ID of the associated choice that was selected
	     * @param {Number} [groupId=-1] ID of group choice is within. Negative number indicates no group
	     * @param {Object} [customProperties] Object containing user defined properties
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: '_addItem',
	    value: function _addItem(value) {
	      var label = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var choiceId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : -1;
	      var groupId = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : -1;
	      var customProperties = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;
	      var placeholder = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : false;
	      var keyCode = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : null;

	      var passedValue = (0, _utils.isType)('String', value) ? value.trim() : value;
	      var passedKeyCode = keyCode;
	      var items = this.store.getItems();
	      var passedLabel = label || passedValue;
	      var passedOptionId = parseInt(choiceId, 10) || -1;

	      // Get group if group ID passed
	      var group = groupId >= 0 ? this.store.getGroupById(groupId) : null;

	      // Generate unique id
	      var id = items ? items.length + 1 : 1;

	      // If a prepended value has been passed, prepend it
	      if (this.config.prependValue) {
	        passedValue = this.config.prependValue + passedValue.toString();
	      }

	      // If an appended value has been passed, append it
	      if (this.config.appendValue) {
	        passedValue += this.config.appendValue.toString();
	      }

	      this.store.dispatch((0, _index3.addItem)(passedValue, passedLabel, id, passedOptionId, groupId, customProperties, placeholder, passedKeyCode));

	      if (this.isSelectOneElement) {
	        this.removeActiveItems(id);
	      }

	      // Trigger change event
	      if (group && group.value) {
	        (0, _utils.triggerEvent)(this.passedElement, 'addItem', {
	          id: id,
	          value: passedValue,
	          label: passedLabel,
	          groupValue: group.value,
	          keyCode: passedKeyCode
	        });
	      } else {
	        (0, _utils.triggerEvent)(this.passedElement, 'addItem', {
	          id: id,
	          value: passedValue,
	          label: passedLabel,
	          keyCode: passedKeyCode
	        });
	      }

	      return this;
	    }

	    /**
	     * Remove item from store
	     * @param {Object} item Item to remove
	     * @return {Object} Class instance
	     * @public
	     */

	  }, {
	    key: '_removeItem',
	    value: function _removeItem(item) {
	      if (!item || !(0, _utils.isType)('Object', item)) {
	        return this;
	      }

	      var id = item.id;
	      var value = item.value;
	      var label = item.label;
	      var choiceId = item.choiceId;
	      var groupId = item.groupId;
	      var group = groupId >= 0 ? this.store.getGroupById(groupId) : null;

	      this.store.dispatch((0, _index3.removeItem)(id, choiceId));

	      if (group && group.value) {
	        (0, _utils.triggerEvent)(this.passedElement, 'removeItem', {
	          id: id,
	          value: value,
	          label: label,
	          groupValue: group.value
	        });
	      } else {
	        (0, _utils.triggerEvent)(this.passedElement, 'removeItem', {
	          id: id,
	          value: value,
	          label: label
	        });
	      }

	      return this;
	    }

	    /**
	     * Add choice to dropdown
	     * @param {String} value Value of choice
	     * @param {String} [label] Label of choice
	     * @param {Boolean} [isSelected=false] Whether choice is selected
	     * @param {Boolean} [isDisabled=false] Whether choice is disabled
	     * @param {Number} [groupId=-1] ID of group choice is within. Negative number indicates no group
	     * @param {Object} [customProperties] Object containing user defined properties
	     * @return
	     * @private
	     */

	  }, {
	    key: '_addChoice',
	    value: function _addChoice(value) {
	      var label = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var isSelected = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      var isDisabled = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
	      var groupId = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : -1;
	      var customProperties = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : null;
	      var placeholder = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : false;
	      var keyCode = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : null;

	      if (typeof value === 'undefined' || value === null) {
	        return;
	      }

	      // Generate unique id
	      var choices = this.store.getChoices();
	      var choiceLabel = label || value;
	      var choiceId = choices ? choices.length + 1 : 1;
	      var choiceElementId = this.baseId + '-' + this.idNames.itemChoice + '-' + choiceId;

	      this.store.dispatch((0, _index3.addChoice)(value, choiceLabel, choiceId, groupId, isDisabled, choiceElementId, customProperties, placeholder, keyCode));

	      if (isSelected) {
	        this._addItem(value, choiceLabel, choiceId, undefined, customProperties, placeholder, keyCode);
	      }
	    }

	    /**
	     * Clear all choices added to the store.
	     * @return
	     * @private
	     */

	  }, {
	    key: '_clearChoices',
	    value: function _clearChoices() {
	      this.store.dispatch((0, _index3.clearChoices)());
	    }

	    /**
	     * Add group to dropdown
	     * @param {Object} group Group to add
	     * @param {Number} id Group ID
	     * @param {String} [valueKey] name of the value property on the object
	     * @param {String} [labelKey] name of the label property on the object
	     * @return
	     * @private
	     */

	  }, {
	    key: '_addGroup',
	    value: function _addGroup(group, id) {
	      var _this21 = this;

	      var valueKey = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'value';
	      var labelKey = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'label';

	      var groupChoices = (0, _utils.isType)('Object', group) ? group.choices : Array.from(group.getElementsByTagName('OPTION'));
	      var groupId = id ? id : Math.floor(new Date().valueOf() * Math.random());
	      var isDisabled = group.disabled ? group.disabled : false;

	      if (groupChoices) {
	        this.store.dispatch((0, _index3.addGroup)(group.label, groupId, true, isDisabled));

	        groupChoices.forEach(function (option) {
	          var isOptDisabled = option.disabled || option.parentNode && option.parentNode.disabled;
	          _this21._addChoice(option[valueKey], (0, _utils.isType)('Object', option) ? option[labelKey] : option.innerHTML, option.selected, isOptDisabled, groupId, option.customProperties, option.placeholder);
	        });
	      } else {
	        this.store.dispatch((0, _index3.addGroup)(group.label, group.id, false, group.disabled));
	      }
	    }

	    /**
	     * Get template from name
	     * @param  {String}    template Name of template to get
	     * @param  {...}       args     Data to pass to template
	     * @return {HTMLElement}        Template
	     * @private
	     */

	  }, {
	    key: '_getTemplate',
	    value: function _getTemplate(template) {
	      if (!template) {
	        return null;
	      }
	      var templates = this.config.templates;

	      for (var _len = arguments.length, args = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	        args[_key - 1] = arguments[_key];
	      }

	      return templates[template].apply(templates, args);
	    }

	    /**
	     * Create HTML element based on type and arguments
	     * @return
	     * @private
	     */

	  }, {
	    key: '_createTemplates',
	    value: function _createTemplates() {
	      var _this22 = this;

	      var globalClasses = this.config.classNames;
	      var templates = {
	        containerOuter: function containerOuter(direction) {
	          return (0, _utils.strToEl)('\n          <div\n            class="' + globalClasses.containerOuter + '"\n            ' + (_this22.isSelectElement ? _this22.config.searchEnabled ? 'role="combobox" aria-autocomplete="list"' : 'role="listbox"' : '') + '\n            data-type="' + _this22.passedElement.type + '"\n            ' + (_this22.isSelectOneElement ? 'tabindex="0"' : '') + '\n            aria-haspopup="true"\n            aria-expanded="false"\n            dir="' + direction + '"\n            >\n          </div>\n        ');
	        },
	        containerInner: function containerInner() {
	          return (0, _utils.strToEl)('\n          <div class="' + globalClasses.containerInner + '"></div>\n        ');
	        },
	        itemList: function itemList() {
	          var _classNames;

	          var localClasses = (0, _classnames2.default)(globalClasses.list, (_classNames = {}, _defineProperty(_classNames, globalClasses.listSingle, _this22.isSelectOneElement), _defineProperty(_classNames, globalClasses.listItems, !_this22.isSelectOneElement), _classNames));

	          return (0, _utils.strToEl)('\n          <div class="' + localClasses + '"></div>\n        ');
	        },
	        placeholder: function placeholder(value) {
	          return (0, _utils.strToEl)('\n          <div class="' + globalClasses.placeholder + '">\n            ' + value + '\n          </div>\n        ');
	        },
	        item: function item(data) {
	          var _classNames2;

	          var localClasses = (0, _classnames2.default)(globalClasses.item, (_classNames2 = {}, _defineProperty(_classNames2, globalClasses.highlightedState, data.highlighted), _defineProperty(_classNames2, globalClasses.itemSelectable, !data.highlighted), _defineProperty(_classNames2, globalClasses.placeholder, data.placeholder), _classNames2));

	          if (_this22.config.removeItemButton) {
	            var _classNames3;

	            localClasses = (0, _classnames2.default)(globalClasses.item, (_classNames3 = {}, _defineProperty(_classNames3, globalClasses.highlightedState, data.highlighted), _defineProperty(_classNames3, globalClasses.itemSelectable, !data.disabled), _defineProperty(_classNames3, globalClasses.placeholder, data.placeholder), _classNames3));

	            return (0, _utils.strToEl)('\n            <div\n              class="' + localClasses + '"\n              data-item\n              data-id="' + data.id + '"\n              data-value="' + data.value + '"\n              data-deletable\n              ' + (data.active ? 'aria-selected="true"' : '') + '\n              ' + (data.disabled ? 'aria-disabled="true"' : '') + '\n              >\n              ' + data.label + '<!--\n           --><button\n                type="button"\n                class="' + globalClasses.button + '"\n                data-button\n                aria-label="Remove item: \'' + data.value + '\'"\n                >\n                Remove item\n              </button>\n            </div>\n          ');
	          }

	          return (0, _utils.strToEl)('\n          <div\n            class="' + localClasses + '"\n            data-item\n            data-id="' + data.id + '"\n            data-value="' + data.value + '"\n            ' + (data.active ? 'aria-selected="true"' : '') + '\n            ' + (data.disabled ? 'aria-disabled="true"' : '') + '\n            >\n            ' + data.label + '\n          </div>\n        ');
	        },
	        choiceList: function choiceList() {
	          return (0, _utils.strToEl)('\n          <div\n            class="' + globalClasses.list + '"\n            dir="ltr"\n            role="listbox"\n            ' + (!_this22.isSelectOneElement ? 'aria-multiselectable="true"' : '') + '\n            >\n          </div>\n        ');
	        },
	        choiceGroup: function choiceGroup(data) {
	          var localClasses = (0, _classnames2.default)(globalClasses.group, _defineProperty({}, globalClasses.itemDisabled, data.disabled));

	          return (0, _utils.strToEl)('\n          <div\n            class="' + localClasses + '"\n            data-group\n            data-id="' + data.id + '"\n            data-value="' + data.value + '"\n            role="group"\n            ' + (data.disabled ? 'aria-disabled="true"' : '') + '\n            >\n            <div class="' + globalClasses.groupHeading + '">' + data.value + '</div>\n          </div>\n        ');
	        },
	        choice: function choice(data) {
	          var _classNames5;

	          var localClasses = (0, _classnames2.default)(globalClasses.item, globalClasses.itemChoice, (_classNames5 = {}, _defineProperty(_classNames5, globalClasses.itemDisabled, data.disabled), _defineProperty(_classNames5, globalClasses.itemSelectable, !data.disabled), _defineProperty(_classNames5, globalClasses.placeholder, data.placeholder), _classNames5));

	          return (0, _utils.strToEl)('\n          <div\n            class="' + localClasses + '"\n            data-select-text="' + _this22.config.itemSelectText + '"\n            data-choice\n            data-id="' + data.id + '"\n            data-value="' + data.value + '"\n            ' + (data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable') + '\n            id="' + data.elementId + '"\n            ' + (data.groupId > 0 ? 'role="treeitem"' : 'role="option"') + '\n            >\n            ' + data.label + '\n          </div>\n        ');
	        },
	        input: function input() {
	          var localClasses = (0, _classnames2.default)(globalClasses.input, globalClasses.inputCloned);

	          return (0, _utils.strToEl)('\n          <input\n            type="text"\n            class="' + localClasses + '"\n            autocomplete="off"\n            autocapitalize="off"\n            spellcheck="false"\n            role="textbox"\n            aria-autocomplete="list"\n            >\n        ');
	        },
	        dropdown: function dropdown() {
	          var localClasses = (0, _classnames2.default)(globalClasses.list, globalClasses.listDropdown);

	          return (0, _utils.strToEl)('\n          <div\n            class="' + localClasses + '"\n            aria-expanded="false"\n            >\n          </div>\n        ');
	        },
	        notice: function notice(label) {
	          var _classNames6;

	          var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';

	          var localClasses = (0, _classnames2.default)(globalClasses.item, globalClasses.itemChoice, (_classNames6 = {}, _defineProperty(_classNames6, globalClasses.noResults, type === 'no-results'), _defineProperty(_classNames6, globalClasses.noChoices, type === 'no-choices'), _classNames6));

	          return (0, _utils.strToEl)('\n          <div class="' + localClasses + '">\n            ' + label + '\n          </div>\n        ');
	        },
	        option: function option(data) {
	          return (0, _utils.strToEl)('\n          <option value="' + data.value + '" selected>' + data.label + '</option>\n        ');
	        }
	      };

	      // User's custom templates
	      var callbackTemplate = this.config.callbackOnCreateTemplates;
	      var userTemplates = {};
	      if (callbackTemplate && (0, _utils.isType)('Function', callbackTemplate)) {
	        userTemplates = callbackTemplate.call(this, _utils.strToEl);
	      }

	      this.config.templates = (0, _utils.extend)(templates, userTemplates);
	    }
	  }, {
	    key: '_setLoading',
	    value: function _setLoading(isLoading) {
	      this.store.dispatch((0, _index3.setIsLoading)(isLoading));
	    }

	    /**
	     * Create DOM structure around passed select element
	     * @return
	     * @private
	     */

	  }, {
	    key: '_createInput',
	    value: function _createInput() {
	      var _this23 = this;

	      var direction = this.passedElement.getAttribute('dir') || 'ltr';
	      var containerOuter = this._getTemplate('containerOuter', direction);
	      var containerInner = this._getTemplate('containerInner');
	      var itemList = this._getTemplate('itemList');
	      var choiceList = this._getTemplate('choiceList');
	      var input = this._getTemplate('input');
	      var dropdown = this._getTemplate('dropdown');

	      this.containerOuter = containerOuter;
	      this.containerInner = containerInner;
	      this.input = input;
	      this.choiceList = choiceList;
	      this.itemList = itemList;
	      this.dropdown = dropdown;

	      // Hide passed input
	      this.passedElement.classList.add(this.config.classNames.input, this.config.classNames.hiddenState);

	      // Remove element from tab index
	      this.passedElement.tabIndex = '-1';

	      // Backup original styles if any
	      var origStyle = this.passedElement.getAttribute('style');

	      if (Boolean(origStyle)) {
	        this.passedElement.setAttribute('data-choice-orig-style', origStyle);
	      }

	      this.passedElement.setAttribute('style', 'display:none;');
	      this.passedElement.setAttribute('aria-hidden', 'true');
	      this.passedElement.setAttribute('data-choice', 'active');

	      // Wrap input in container preserving DOM ordering
	      (0, _utils.wrap)(this.passedElement, containerInner);

	      // Wrapper inner container with outer container
	      (0, _utils.wrap)(containerInner, containerOuter);

	      if (this.isSelectOneElement) {
	        input.placeholder = this.config.searchPlaceholderValue || '';
	      } else if (this.placeholder) {
	        input.placeholder = this.placeholder;
	        input.style.width = (0, _utils.getWidthOfInput)(input);
	      }

	      if (!this.config.addItems) {
	        this.disable();
	      }

	      containerOuter.appendChild(containerInner);
	      containerOuter.appendChild(dropdown);
	      containerInner.appendChild(itemList);

	      if (!this.isTextElement) {
	        dropdown.appendChild(choiceList);
	      }

	      if (this.isSelectMultipleElement || this.isTextElement) {
	        containerInner.appendChild(input);
	      } else if (this.canSearch) {
	        dropdown.insertBefore(input, dropdown.firstChild);
	      }

	      if (this.isSelectElement) {
	        var passedGroups = Array.from(this.passedElement.getElementsByTagName('OPTGROUP'));

	        this.highlightPosition = 0;
	        this.isSearching = false;

	        this._setLoading(true);

	        if (passedGroups && passedGroups.length) {
	          passedGroups.forEach(function (group) {
	            _this23._addGroup(group, group.id || null);
	          });
	        } else {
	          var passedOptions = Array.from(this.passedElement.options);
	          var filter = this.config.sortFilter;
	          var allChoices = this.presetChoices;

	          // Create array of options from option elements
	          passedOptions.forEach(function (o) {
	            allChoices.push({
	              value: o.value,
	              label: o.innerHTML,
	              selected: o.selected,
	              disabled: o.disabled || o.parentNode.disabled,
	              placeholder: o.hasAttribute('placeholder')
	            });
	          });

	          // If sorting is enabled or the user is searching, filter choices
	          if (this.config.shouldSort) {
	            allChoices.sort(filter);
	          }

	          // Determine whether there is a selected choice
	          var hasSelectedChoice = allChoices.some(function (choice) {
	            return choice.selected;
	          });

	          // Add each choice
	          allChoices.forEach(function (choice, index) {
	            // Pre-select first choice if it's a single select
	            if (_this23.isSelectOneElement) {
	              // If there is a selected choice already or the choice is not
	              // the first in the array, add each choice normally
	              // Otherwise pre-select the first choice in the array
	              var shouldPreselect = hasSelectedChoice || !hasSelectedChoice && index > 0;
	              _this23._addChoice(choice.value, choice.label, shouldPreselect ? choice.selected : true, shouldPreselect ? choice.disabled : false, undefined, choice.customProperties, choice.placeholder);
	            } else {
	              _this23._addChoice(choice.value, choice.label, choice.selected, choice.disabled, undefined, choice.customProperties, choice.placeholder);
	            }
	          });
	        }

	        this._setLoading(false);
	      } else if (this.isTextElement) {
	        // Add any preset values seperated by delimiter
	        this.presetItems.forEach(function (item) {
	          var itemType = (0, _utils.getType)(item);
	          if (itemType === 'Object') {
	            if (!item.value) {
	              return;
	            }
	            _this23._addItem(item.value, item.label, item.id, undefined, item.customProperties, item.placeholder);
	          } else if (itemType === 'String') {
	            _this23._addItem(item);
	          }
	        });
	      }
	    }

	    /*=====  End of Private functions  ======*/

	  }]);

	  return Choices;
	}();

		module.exports = Choices;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

	/**
	 * @license
	 * Fuse - Lightweight fuzzy-search
	 *
	 * Copyright (c) 2012-2016 Kirollos Risk <kirollos@gmail.com>.
	 * All Rights Reserved. Apache Software License 2.0
	 *
	 * Licensed under the Apache License, Version 2.0 (the "License")
	 * you may not use this file except in compliance with the License.
	 * You may obtain a copy of the License at
	 *
	 * http://www.apache.org/licenses/LICENSE-2.0
	 *
	 * Unless required by applicable law or agreed to in writing, software
	 * distributed under the License is distributed on an "AS IS" BASIS,
	 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 * See the License for the specific language governing permissions and
	 * limitations under the License.
	 */
	;(function (global) {
	  'use strict'

	  /** @type {function(...*)} */
	  function log () {
	    console.log.apply(console, arguments)
	  }

	  var defaultOptions = {
	    // The name of the identifier property. If specified, the returned result will be a list
	    // of the items' dentifiers, otherwise it will be a list of the items.
	    id: null,

	    // Indicates whether comparisons should be case sensitive.

	    caseSensitive: false,

	    // An array of values that should be included from the searcher's output. When this array
	    // contains elements, each result in the list will be of the form `{ item: ..., include1: ..., include2: ... }`.
	    // Values you can include are `score`, `matchedLocations`
	    include: [],

	    // Whether to sort the result list, by score
	    shouldSort: true,

	    // The search function to use
	    // Note that the default search function ([[Function]]) must conform to the following API:
	    //
	    //  @param pattern The pattern string to search
	    //  @param options The search option
	    //  [[Function]].constructor = function(pattern, options)
	    //
	    //  @param text: the string to search in for the pattern
	    //  @return Object in the form of:
	    //    - isMatch: boolean
	    //    - score: Int
	    //  [[Function]].prototype.search = function(text)
	    searchFn: BitapSearcher,

	    // Default sort function
	    sortFn: function (a, b) {
	      return a.score - b.score
	    },

	    // The get function to use when fetching an object's properties.
	    // The default will search nested paths *ie foo.bar.baz*
	    getFn: deepValue,

	    // List of properties that will be searched. This also supports nested properties.
	    keys: [],

	    // Will print to the console. Useful for debugging.
	    verbose: false,

	    // When true, the search algorithm will search individual words **and** the full string,
	    // computing the final score as a function of both. Note that when `tokenize` is `true`,
	    // the `threshold`, `distance`, and `location` are inconsequential for individual tokens.
	    tokenize: false,

	    // When true, the result set will only include records that match all tokens. Will only work
	    // if `tokenize` is also true.
	    matchAllTokens: false,

	    // Regex used to separate words when searching. Only applicable when `tokenize` is `true`.
	    tokenSeparator: / +/g,

	    // Minimum number of characters that must be matched before a result is considered a match
	    minMatchCharLength: 1,

	    // When true, the algorithm continues searching to the end of the input even if a perfect
	    // match is found before the end of the same input.
	    findAllMatches: false
	  }

	  /**
	   * @constructor
	   * @param {!Array} list
	   * @param {!Object<string, *>} options
	   */
	  function Fuse (list, options) {
	    var key

	    this.list = list
	    this.options = options = options || {}

	    for (key in defaultOptions) {
	      if (!defaultOptions.hasOwnProperty(key)) {
	        continue;
	      }
	      // Add boolean type options
	      if (typeof defaultOptions[key] === 'boolean') {
	        this.options[key] = key in options ? options[key] : defaultOptions[key];
	      // Add all other options
	      } else {
	        this.options[key] = options[key] || defaultOptions[key]
	      }
	    }
	  }

	  Fuse.VERSION = '2.7.3'

	  /**
	   * Sets a new list for Fuse to match against.
	   * @param {!Array} list
	   * @return {!Array} The newly set list
	   * @public
	   */
	  Fuse.prototype.set = function (list) {
	    this.list = list
	    return list
	  }

	  Fuse.prototype.search = function (pattern) {
	    if (this.options.verbose) log('\nSearch term:', pattern, '\n')

	    this.pattern = pattern
	    this.results = []
	    this.resultMap = {}
	    this._keyMap = null

	    this._prepareSearchers()
	    this._startSearch()
	    this._computeScore()
	    this._sort()

	    var output = this._format()
	    return output
	  }

	  Fuse.prototype._prepareSearchers = function () {
	    var options = this.options
	    var pattern = this.pattern
	    var searchFn = options.searchFn
	    var tokens = pattern.split(options.tokenSeparator)
	    var i = 0
	    var len = tokens.length

	    if (this.options.tokenize) {
	      this.tokenSearchers = []
	      for (; i < len; i++) {
	        this.tokenSearchers.push(new searchFn(tokens[i], options))
	      }
	    }
	    this.fullSeacher = new searchFn(pattern, options)
	  }

	  Fuse.prototype._startSearch = function () {
	    var options = this.options
	    var getFn = options.getFn
	    var list = this.list
	    var listLen = list.length
	    var keys = this.options.keys
	    var keysLen = keys.length
	    var key
	    var weight
	    var item = null
	    var i
	    var j

	    // Check the first item in the list, if it's a string, then we assume
	    // that every item in the list is also a string, and thus it's a flattened array.
	    if (typeof list[0] === 'string') {
	      // Iterate over every item
	      for (i = 0; i < listLen; i++) {
	        this._analyze('', list[i], i, i)
	      }
	    } else {
	      this._keyMap = {}
	      // Otherwise, the first item is an Object (hopefully), and thus the searching
	      // is done on the values of the keys of each item.
	      // Iterate over every item
	      for (i = 0; i < listLen; i++) {
	        item = list[i]
	        // Iterate over every key
	        for (j = 0; j < keysLen; j++) {
	          key = keys[j]
	          if (typeof key !== 'string') {
	            weight = (1 - key.weight) || 1
	            this._keyMap[key.name] = {
	              weight: weight
	            }
	            if (key.weight <= 0 || key.weight > 1) {
	              throw new Error('Key weight has to be > 0 and <= 1')
	            }
	            key = key.name
	          } else {
	            this._keyMap[key] = {
	              weight: 1
	            }
	          }
	          this._analyze(key, getFn(item, key, []), item, i)
	        }
	      }
	    }
	  }

	  Fuse.prototype._analyze = function (key, text, entity, index) {
	    var options = this.options
	    var words
	    var scores
	    var exists = false
	    var existingResult
	    var averageScore
	    var finalScore
	    var scoresLen
	    var mainSearchResult
	    var tokenSearcher
	    var termScores
	    var word
	    var tokenSearchResult
	    var hasMatchInText
	    var checkTextMatches
	    var i
	    var j

	    // Check if the text can be searched
	    if (text === undefined || text === null) {
	      return
	    }

	    scores = []

	    var numTextMatches = 0

	    if (typeof text === 'string') {
	      words = text.split(options.tokenSeparator)

	      if (options.verbose) log('---------\nKey:', key)

	      if (this.options.tokenize) {
	        for (i = 0; i < this.tokenSearchers.length; i++) {
	          tokenSearcher = this.tokenSearchers[i]

	          if (options.verbose) log('Pattern:', tokenSearcher.pattern)

	          termScores = []
	          hasMatchInText = false

	          for (j = 0; j < words.length; j++) {
	            word = words[j]
	            tokenSearchResult = tokenSearcher.search(word)
	            var obj = {}
	            if (tokenSearchResult.isMatch) {
	              obj[word] = tokenSearchResult.score
	              exists = true
	              hasMatchInText = true
	              scores.push(tokenSearchResult.score)
	            } else {
	              obj[word] = 1
	              if (!this.options.matchAllTokens) {
	                scores.push(1)
	              }
	            }
	            termScores.push(obj)
	          }

	          if (hasMatchInText) {
	            numTextMatches++
	          }

	          if (options.verbose) log('Token scores:', termScores)
	        }

	        averageScore = scores[0]
	        scoresLen = scores.length
	        for (i = 1; i < scoresLen; i++) {
	          averageScore += scores[i]
	        }
	        averageScore = averageScore / scoresLen

	        if (options.verbose) log('Token score average:', averageScore)
	      }

	      mainSearchResult = this.fullSeacher.search(text)
	      if (options.verbose) log('Full text score:', mainSearchResult.score)

	      finalScore = mainSearchResult.score
	      if (averageScore !== undefined) {
	        finalScore = (finalScore + averageScore) / 2
	      }

	      if (options.verbose) log('Score average:', finalScore)

	      checkTextMatches = (this.options.tokenize && this.options.matchAllTokens) ? numTextMatches >= this.tokenSearchers.length : true

	      if (options.verbose) log('Check Matches', checkTextMatches)

	      // If a match is found, add the item to <rawResults>, including its score
	      if ((exists || mainSearchResult.isMatch) && checkTextMatches) {
	        // Check if the item already exists in our results
	        existingResult = this.resultMap[index]

	        if (existingResult) {
	          // Use the lowest score
	          // existingResult.score, bitapResult.score
	          existingResult.output.push({
	            key: key,
	            score: finalScore,
	            matchedIndices: mainSearchResult.matchedIndices
	          })
	        } else {
	          // Add it to the raw result list
	          this.resultMap[index] = {
	            item: entity,
	            output: [{
	              key: key,
	              score: finalScore,
	              matchedIndices: mainSearchResult.matchedIndices
	            }]
	          }

	          this.results.push(this.resultMap[index])
	        }
	      }
	    } else if (isArray(text)) {
	      for (i = 0; i < text.length; i++) {
	        this._analyze(key, text[i], entity, index)
	      }
	    }
	  }

	  Fuse.prototype._computeScore = function () {
	    var i
	    var j
	    var keyMap = this._keyMap
	    var totalScore
	    var output
	    var scoreLen
	    var score
	    var weight
	    var results = this.results
	    var bestScore
	    var nScore

	    if (this.options.verbose) log('\n\nComputing score:\n')

	    for (i = 0; i < results.length; i++) {
	      totalScore = 0
	      output = results[i].output
	      scoreLen = output.length

	      bestScore = 1

	      for (j = 0; j < scoreLen; j++) {
	        score = output[j].score
	        weight = keyMap ? keyMap[output[j].key].weight : 1

	        nScore = score * weight

	        if (weight !== 1) {
	          bestScore = Math.min(bestScore, nScore)
	        } else {
	          totalScore += nScore
	          output[j].nScore = nScore
	        }
	      }

	      if (bestScore === 1) {
	        results[i].score = totalScore / scoreLen
	      } else {
	        results[i].score = bestScore
	      }

	      if (this.options.verbose) log(results[i])
	    }
	  }

	  Fuse.prototype._sort = function () {
	    var options = this.options
	    if (options.shouldSort) {
	      if (options.verbose) log('\n\nSorting....')
	      this.results.sort(options.sortFn)
	    }
	  }

	  Fuse.prototype._format = function () {
	    var options = this.options
	    var getFn = options.getFn
	    var finalOutput = []
	    var i
	    var len
	    var results = this.results
	    var replaceValue
	    var getItemAtIndex
	    var include = options.include

	    if (options.verbose) log('\n\nOutput:\n\n', results)

	    // Helper function, here for speed-up, which replaces the item with its value,
	    // if the options specifies it,
	    replaceValue = options.id ? function (index) {
	      results[index].item = getFn(results[index].item, options.id, [])[0]
	    } : function () {}

	    getItemAtIndex = function (index) {
	      var record = results[index]
	      var data
	      var j
	      var output
	      var _item
	      var _result

	      // If `include` has values, put the item in the result
	      if (include.length > 0) {
	        data = {
	          item: record.item
	        }
	        if (include.indexOf('matches') !== -1) {
	          output = record.output
	          data.matches = []
	          for (j = 0; j < output.length; j++) {
	            _item = output[j]
	            _result = {
	              indices: _item.matchedIndices
	            }
	            if (_item.key) {
	              _result.key = _item.key
	            }
	            data.matches.push(_result)
	          }
	        }

	        if (include.indexOf('score') !== -1) {
	          data.score = results[index].score
	        }

	      } else {
	        data = record.item
	      }

	      return data
	    }

	    // From the results, push into a new array only the item identifier (if specified)
	    // of the entire item.  This is because we don't want to return the <results>,
	    // since it contains other metadata
	    for (i = 0, len = results.length; i < len; i++) {
	      replaceValue(i)
	      finalOutput.push(getItemAtIndex(i))
	    }

	    return finalOutput
	  }

	  // Helpers

	  function deepValue (obj, path, list) {
	    var firstSegment
	    var remaining
	    var dotIndex
	    var value
	    var i
	    var len

	    if (!path) {
	      // If there's no path left, we've gotten to the object we care about.
	      list.push(obj)
	    } else {
	      dotIndex = path.indexOf('.')

	      if (dotIndex !== -1) {
	        firstSegment = path.slice(0, dotIndex)
	        remaining = path.slice(dotIndex + 1)
	      } else {
	        firstSegment = path
	      }

	      value = obj[firstSegment]
	      if (value !== null && value !== undefined) {
	        if (!remaining && (typeof value === 'string' || typeof value === 'number')) {
	          list.push(value)
	        } else if (isArray(value)) {
	          // Search each item in the array.
	          for (i = 0, len = value.length; i < len; i++) {
	            deepValue(value[i], remaining, list)
	          }
	        } else if (remaining) {
	          // An object. Recurse further.
	          deepValue(value, remaining, list)
	        }
	      }
	    }

	    return list
	  }

	  function isArray (obj) {
	    return Object.prototype.toString.call(obj) === '[object Array]'
	  }

	  /**
	   * Adapted from "Diff, Match and Patch", by Google
	   *
	   *   http://code.google.com/p/google-diff-match-patch/
	   *
	   * Modified by: Kirollos Risk <kirollos@gmail.com>
	   * -----------------------------------------------
	   * Details: the algorithm and structure was modified to allow the creation of
	   * <Searcher> instances with a <search> method which does the actual
	   * bitap search. The <pattern> (the string that is searched for) is only defined
	   * once per instance and thus it eliminates redundant re-creation when searching
	   * over a list of strings.
	   *
	   * Licensed under the Apache License, Version 2.0 (the "License")
	   * you may not use this file except in compliance with the License.
	   *
	   * @constructor
	   */
	  function BitapSearcher (pattern, options) {
	    options = options || {}
	    this.options = options
	    this.options.location = options.location || BitapSearcher.defaultOptions.location
	    this.options.distance = 'distance' in options ? options.distance : BitapSearcher.defaultOptions.distance
	    this.options.threshold = 'threshold' in options ? options.threshold : BitapSearcher.defaultOptions.threshold
	    this.options.maxPatternLength = options.maxPatternLength || BitapSearcher.defaultOptions.maxPatternLength

	    this.pattern = options.caseSensitive ? pattern : pattern.toLowerCase()
	    this.patternLen = pattern.length

	    if (this.patternLen <= this.options.maxPatternLength) {
	      this.matchmask = 1 << (this.patternLen - 1)
	      this.patternAlphabet = this._calculatePatternAlphabet()
	    }
	  }

	  BitapSearcher.defaultOptions = {
	    // Approximately where in the text is the pattern expected to be found?
	    location: 0,

	    // Determines how close the match must be to the fuzzy location (specified above).
	    // An exact letter match which is 'distance' characters away from the fuzzy location
	    // would score as a complete mismatch. A distance of '0' requires the match be at
	    // the exact location specified, a threshold of '1000' would require a perfect match
	    // to be within 800 characters of the fuzzy location to be found using a 0.8 threshold.
	    distance: 100,

	    // At what point does the match algorithm give up. A threshold of '0.0' requires a perfect match
	    // (of both letters and location), a threshold of '1.0' would match anything.
	    threshold: 0.6,

	    // Machine word size
	    maxPatternLength: 32
	  }

	  /**
	   * Initialize the alphabet for the Bitap algorithm.
	   * @return {Object} Hash of character locations.
	   * @private
	   */
	  BitapSearcher.prototype._calculatePatternAlphabet = function () {
	    var mask = {},
	      i = 0

	    for (i = 0; i < this.patternLen; i++) {
	      mask[this.pattern.charAt(i)] = 0
	    }

	    for (i = 0; i < this.patternLen; i++) {
	      mask[this.pattern.charAt(i)] |= 1 << (this.pattern.length - i - 1)
	    }

	    return mask
	  }

	  /**
	   * Compute and return the score for a match with `e` errors and `x` location.
	   * @param {number} errors Number of errors in match.
	   * @param {number} location Location of match.
	   * @return {number} Overall score for match (0.0 = good, 1.0 = bad).
	   * @private
	   */
	  BitapSearcher.prototype._bitapScore = function (errors, location) {
	    var accuracy = errors / this.patternLen,
	      proximity = Math.abs(this.options.location - location)

	    if (!this.options.distance) {
	      // Dodge divide by zero error.
	      return proximity ? 1.0 : accuracy
	    }
	    return accuracy + (proximity / this.options.distance)
	  }

	  /**
	   * Compute and return the result of the search
	   * @param {string} text The text to search in
	   * @return {{isMatch: boolean, score: number}} Literal containing:
	   *                          isMatch - Whether the text is a match or not
	   *                          score - Overall score for the match
	   * @public
	   */
	  BitapSearcher.prototype.search = function (text) {
	    var options = this.options
	    var i
	    var j
	    var textLen
	    var findAllMatches
	    var location
	    var threshold
	    var bestLoc
	    var binMin
	    var binMid
	    var binMax
	    var start, finish
	    var bitArr
	    var lastBitArr
	    var charMatch
	    var score
	    var locations
	    var matches
	    var isMatched
	    var matchMask
	    var matchedIndices
	    var matchesLen
	    var match

	    text = options.caseSensitive ? text : text.toLowerCase()

	    if (this.pattern === text) {
	      // Exact match
	      return {
	        isMatch: true,
	        score: 0,
	        matchedIndices: [[0, text.length - 1]]
	      }
	    }

	    // When pattern length is greater than the machine word length, just do a a regex comparison
	    if (this.patternLen > options.maxPatternLength) {
	      matches = text.match(new RegExp(this.pattern.replace(options.tokenSeparator, '|')))
	      isMatched = !!matches

	      if (isMatched) {
	        matchedIndices = []
	        for (i = 0, matchesLen = matches.length; i < matchesLen; i++) {
	          match = matches[i]
	          matchedIndices.push([text.indexOf(match), match.length - 1])
	        }
	      }

	      return {
	        isMatch: isMatched,
	        // TODO: revisit this score
	        score: isMatched ? 0.5 : 1,
	        matchedIndices: matchedIndices
	      }
	    }

	    findAllMatches = options.findAllMatches

	    location = options.location
	    // Set starting location at beginning text and initialize the alphabet.
	    textLen = text.length
	    // Highest score beyond which we give up.
	    threshold = options.threshold
	    // Is there a nearby exact match? (speedup)
	    bestLoc = text.indexOf(this.pattern, location)

	    // a mask of the matches
	    matchMask = []
	    for (i = 0; i < textLen; i++) {
	      matchMask[i] = 0
	    }

	    if (bestLoc != -1) {
	      threshold = Math.min(this._bitapScore(0, bestLoc), threshold)
	      // What about in the other direction? (speed up)
	      bestLoc = text.lastIndexOf(this.pattern, location + this.patternLen)

	      if (bestLoc != -1) {
	        threshold = Math.min(this._bitapScore(0, bestLoc), threshold)
	      }
	    }

	    bestLoc = -1
	    score = 1
	    locations = []
	    binMax = this.patternLen + textLen

	    for (i = 0; i < this.patternLen; i++) {
	      // Scan for the best match; each iteration allows for one more error.
	      // Run a binary search to determine how far from the match location we can stray
	      // at this error level.
	      binMin = 0
	      binMid = binMax
	      while (binMin < binMid) {
	        if (this._bitapScore(i, location + binMid) <= threshold) {
	          binMin = binMid
	        } else {
	          binMax = binMid
	        }
	        binMid = Math.floor((binMax - binMin) / 2 + binMin)
	      }

	      // Use the result from this iteration as the maximum for the next.
	      binMax = binMid
	      start = Math.max(1, location - binMid + 1)
	      if (findAllMatches) {
	        finish = textLen;
	      } else {
	        finish = Math.min(location + binMid, textLen) + this.patternLen
	      }

	      // Initialize the bit array
	      bitArr = Array(finish + 2)

	      bitArr[finish + 1] = (1 << i) - 1

	      for (j = finish; j >= start; j--) {
	        charMatch = this.patternAlphabet[text.charAt(j - 1)]

	        if (charMatch) {
	          matchMask[j - 1] = 1
	        }

	        bitArr[j] = ((bitArr[j + 1] << 1) | 1) & charMatch

	        if (i !== 0) {
	          // Subsequent passes: fuzzy match.
	          bitArr[j] |= (((lastBitArr[j + 1] | lastBitArr[j]) << 1) | 1) | lastBitArr[j + 1]
	        }
	        if (bitArr[j] & this.matchmask) {
	          score = this._bitapScore(i, j - 1)

	          // This match will almost certainly be better than any existing match.
	          // But check anyway.
	          if (score <= threshold) {
	            // Indeed it is
	            threshold = score
	            bestLoc = j - 1
	            locations.push(bestLoc)

	            // Already passed loc, downhill from here on in.
	            if (bestLoc <= location) {
	              break
	            }

	            // When passing loc, don't exceed our current distance from loc.
	            start = Math.max(1, 2 * location - bestLoc)
	          }
	        }
	      }

	      // No hope for a (better) match at greater error levels.
	      if (this._bitapScore(i + 1, location) > threshold) {
	        break
	      }
	      lastBitArr = bitArr
	    }

	    matchedIndices = this._getMatchedIndices(matchMask)

	    // Count exact matches (those with a score of 0) to be "almost" exact
	    return {
	      isMatch: bestLoc >= 0,
	      score: score === 0 ? 0.001 : score,
	      matchedIndices: matchedIndices
	    }
	  }

	  BitapSearcher.prototype._getMatchedIndices = function (matchMask) {
	    var matchedIndices = []
	    var start = -1
	    var end = -1
	    var i = 0
	    var match
	    var len = matchMask.length
	    for (; i < len; i++) {
	      match = matchMask[i]
	      if (match && start === -1) {
	        start = i
	      } else if (!match && start !== -1) {
	        end = i - 1
	        if ((end - start) + 1 >= this.options.minMatchCharLength) {
	            matchedIndices.push([start, end])
	        }
	        start = -1
	      }
	    }
	    if (matchMask[i - 1]) {
	      if ((i-1 - start) + 1 >= this.options.minMatchCharLength) {
	        matchedIndices.push([start, i - 1])
	      }
	    }
	    return matchedIndices
	  }

	  // Export to Common JS Loader
	  if (true) {
	    // Node. Does not work with strict CommonJS, but
	    // only CommonJS-like environments that support module.exports,
	    // like Node.
	    module.exports = Fuse
	  } else if (typeof define === 'function' && define.amd) {
	    // AMD. Register as an anonymous module.
	    define(function () {
	      return Fuse
	    })
	  } else {
	    // Browser globals (root is window)
	    global.Fuse = Fuse
	  }

	})(this);


/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!
	  Copyright (c) 2016 Jed Watson.
	  Licensed under the MIT License (MIT), see
	  http://jedwatson.github.io/classnames
	*/
	/* global define */

	(function () {
		'use strict';

		var hasOwn = {}.hasOwnProperty;

		function classNames () {
			var classes = [];

			for (var i = 0; i < arguments.length; i++) {
				var arg = arguments[i];
				if (!arg) continue;

				var argType = typeof arg;

				if (argType === 'string' || argType === 'number') {
					classes.push(arg);
				} else if (Array.isArray(arg)) {
					classes.push(classNames.apply(null, arg));
				} else if (argType === 'object') {
					for (var key in arg) {
						if (hasOwn.call(arg, key) && arg[key]) {
							classes.push(key);
						}
					}
				}
			}

			return classes.join(' ');
		}

		if (typeof module !== 'undefined' && module.exports) {
			module.exports = classNames;
		} else if (true) {
			// register as 'classnames', consistent with npm package name
			!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = function () {
				return classNames;
			}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
		} else {
			window.classNames = classNames;
		}
	}());


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _redux = __webpack_require__(5);

	var _index = __webpack_require__(26);

	var _index2 = _interopRequireDefault(_index);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var Store = function () {
	  function Store() {
	    _classCallCheck(this, Store);

	    this.store = (0, _redux.createStore)(_index2.default, window.devToolsExtension ? window.devToolsExtension() : undefined);
	  }

	  /**
	   * Get store object (wrapping Redux method)
	   * @return {Object} State
	   */


	  _createClass(Store, [{
	    key: 'getState',
	    value: function getState() {
	      return this.store.getState();
	    }

	    /**
	     * Dispatch event to store (wrapped Redux method)
	     * @param  {Function} action Action function to trigger
	     * @return
	     */

	  }, {
	    key: 'dispatch',
	    value: function dispatch(action) {
	      this.store.dispatch(action);
	    }

	    /**
	     * Subscribe store to function call (wrapped Redux method)
	     * @param  {Function} onChange Function to trigger when state changes
	     * @return
	     */

	  }, {
	    key: 'subscribe',
	    value: function subscribe(onChange) {
	      this.store.subscribe(onChange);
	    }

	    /**
	     * Get loading state from store
	     * @return {Boolean} Loading State
	     */

	  }, {
	    key: 'isLoading',
	    value: function isLoading() {
	      var state = this.store.getState();
	      return state.general.loading;
	    }

	    /**
	     * Get items from store
	     * @return {Array} Item objects
	     */

	  }, {
	    key: 'getItems',
	    value: function getItems() {
	      var state = this.store.getState();
	      return state.items;
	    }

	    /**
	     * Get active items from store
	     * @return {Array} Item objects
	     */

	  }, {
	    key: 'getItemsFilteredByActive',
	    value: function getItemsFilteredByActive() {
	      var items = this.getItems();
	      var values = items.filter(function (item) {
	        return item.active === true;
	      }, []);

	      return values;
	    }

	    /**
	     * Get items from store reduced to just their values
	     * @return {Array} Item objects
	     */

	  }, {
	    key: 'getItemsReducedToValues',
	    value: function getItemsReducedToValues() {
	      var items = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getItems();

	      var values = items.reduce(function (prev, current) {
	        prev.push(current.value);
	        return prev;
	      }, []);

	      return values;
	    }

	    /**
	     * Get choices from store
	     * @return {Array} Option objects
	     */

	  }, {
	    key: 'getChoices',
	    value: function getChoices() {
	      var state = this.store.getState();
	      return state.choices;
	    }

	    /**
	     * Get active choices from store
	     * @return {Array} Option objects
	     */

	  }, {
	    key: 'getChoicesFilteredByActive',
	    value: function getChoicesFilteredByActive() {
	      var choices = this.getChoices();
	      var values = choices.filter(function (choice) {
	        return choice.active === true;
	      });

	      return values;
	    }

	    /**
	     * Get selectable choices from store
	     * @return {Array} Option objects
	     */

	  }, {
	    key: 'getChoicesFilteredBySelectable',
	    value: function getChoicesFilteredBySelectable() {
	      var choices = this.getChoices();
	      var values = choices.filter(function (choice) {
	        return choice.disabled !== true;
	      });

	      return values;
	    }

	    /**
	     * Get choices that can be searched (excluding placeholders)
	     * @return {Array} Option objects
	     */

	  }, {
	    key: 'getSearchableChoices',
	    value: function getSearchableChoices() {
	      var filtered = this.getChoicesFilteredBySelectable();
	      return filtered.filter(function (choice) {
	        return choice.placeholder !== true;
	      });
	    }

	    /**
	     * Get single choice by it's ID
	     * @return {Object} Found choice
	     */

	  }, {
	    key: 'getChoiceById',
	    value: function getChoiceById(id) {
	      if (id) {
	        var choices = this.getChoicesFilteredByActive();
	        var foundChoice = choices.find(function (choice) {
	          return choice.id === parseInt(id, 10);
	        });
	        return foundChoice;
	      }
	      return false;
	    }

	    /**
	     * Get groups from store
	     * @return {Array} Group objects
	     */

	  }, {
	    key: 'getGroups',
	    value: function getGroups() {
	      var state = this.store.getState();
	      return state.groups;
	    }

	    /**
	     * Get active groups from store
	     * @return {Array} Group objects
	     */

	  }, {
	    key: 'getGroupsFilteredByActive',
	    value: function getGroupsFilteredByActive() {
	      var groups = this.getGroups();
	      var choices = this.getChoices();

	      var values = groups.filter(function (group) {
	        var isActive = group.active === true && group.disabled === false;
	        var hasActiveOptions = choices.some(function (choice) {
	          return choice.active === true && choice.disabled === false;
	        });
	        return isActive && hasActiveOptions;
	      }, []);

	      return values;
	    }

	    /**
	     * Get group by group id
	     * @param  {Number} id Group ID
	     * @return {Object}    Group data
	     */

	  }, {
	    key: 'getGroupById',
	    value: function getGroupById(id) {
	      var groups = this.getGroups();
	      var foundGroup = groups.find(function (group) {
	        return group.id === id;
	      });

	      return foundGroup;
	    }

	    /**
	     * Get placeholder choice from store
	     * @return {Object} Found placeholder
	     */

	  }, {
	    key: 'getPlaceholderChoice',
	    value: function getPlaceholderChoice() {
	      var choices = this.getChoices();
	      var placeholderChoice = [].concat(_toConsumableArray(choices)).reverse().find(function (choice) {
	        return choice.placeholder === true;
	      });

	      return placeholderChoice;
	    }
	  }]);

	  return Store;
	}();

	exports.default = Store;


		module.exports = Store;

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;
	exports.compose = exports.applyMiddleware = exports.bindActionCreators = exports.combineReducers = exports.createStore = undefined;

	var _createStore = __webpack_require__(6);

	var _createStore2 = _interopRequireDefault(_createStore);

	var _combineReducers = __webpack_require__(21);

	var _combineReducers2 = _interopRequireDefault(_combineReducers);

	var _bindActionCreators = __webpack_require__(23);

	var _bindActionCreators2 = _interopRequireDefault(_bindActionCreators);

	var _applyMiddleware = __webpack_require__(24);

	var _applyMiddleware2 = _interopRequireDefault(_applyMiddleware);

	var _compose = __webpack_require__(25);

	var _compose2 = _interopRequireDefault(_compose);

	var _warning = __webpack_require__(22);

	var _warning2 = _interopRequireDefault(_warning);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

	/*
	* This is a dummy function to check if the function name has been altered by minification.
	* If the function has been minified and NODE_ENV !== 'production', warn the user.
	*/
	function isCrushed() {}

	if (false) {
	  (0, _warning2['default'])('You are currently using minified code outside of NODE_ENV === \'production\'. ' + 'This means that you are running a slower development build of Redux. ' + 'You can use loose-envify (https://github.com/zertosh/loose-envify) for browserify ' + 'or DefinePlugin for webpack (http://stackoverflow.com/questions/30030031) ' + 'to ensure you have the correct code for your production build.');
	}

	exports.createStore = _createStore2['default'];
	exports.combineReducers = _combineReducers2['default'];
	exports.bindActionCreators = _bindActionCreators2['default'];
	exports.applyMiddleware = _applyMiddleware2['default'];
	exports.compose = _compose2['default'];

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;
	exports.ActionTypes = undefined;
	exports['default'] = createStore;

	var _isPlainObject = __webpack_require__(7);

	var _isPlainObject2 = _interopRequireDefault(_isPlainObject);

	var _symbolObservable = __webpack_require__(17);

	var _symbolObservable2 = _interopRequireDefault(_symbolObservable);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

	/**
	 * These are private action types reserved by Redux.
	 * For any unknown actions, you must return the current state.
	 * If the current state is undefined, you must return the initial state.
	 * Do not reference these action types directly in your code.
	 */
	var ActionTypes = exports.ActionTypes = {
	  INIT: '@@redux/INIT'

	  /**
	   * Creates a Redux store that holds the state tree.
	   * The only way to change the data in the store is to call `dispatch()` on it.
	   *
	   * There should only be a single store in your app. To specify how different
	   * parts of the state tree respond to actions, you may combine several reducers
	   * into a single reducer function by using `combineReducers`.
	   *
	   * @param {Function} reducer A function that returns the next state tree, given
	   * the current state tree and the action to handle.
	   *
	   * @param {any} [preloadedState] The initial state. You may optionally specify it
	   * to hydrate the state from the server in universal apps, or to restore a
	   * previously serialized user session.
	   * If you use `combineReducers` to produce the root reducer function, this must be
	   * an object with the same shape as `combineReducers` keys.
	   *
	   * @param {Function} [enhancer] The store enhancer. You may optionally specify it
	   * to enhance the store with third-party capabilities such as middleware,
	   * time travel, persistence, etc. The only store enhancer that ships with Redux
	   * is `applyMiddleware()`.
	   *
	   * @returns {Store} A Redux store that lets you read the state, dispatch actions
	   * and subscribe to changes.
	   */
	};function createStore(reducer, preloadedState, enhancer) {
	  var _ref2;

	  if (typeof preloadedState === 'function' && typeof enhancer === 'undefined') {
	    enhancer = preloadedState;
	    preloadedState = undefined;
	  }

	  if (typeof enhancer !== 'undefined') {
	    if (typeof enhancer !== 'function') {
	      throw new Error('Expected the enhancer to be a function.');
	    }

	    return enhancer(createStore)(reducer, preloadedState);
	  }

	  if (typeof reducer !== 'function') {
	    throw new Error('Expected the reducer to be a function.');
	  }

	  var currentReducer = reducer;
	  var currentState = preloadedState;
	  var currentListeners = [];
	  var nextListeners = currentListeners;
	  var isDispatching = false;

	  function ensureCanMutateNextListeners() {
	    if (nextListeners === currentListeners) {
	      nextListeners = currentListeners.slice();
	    }
	  }

	  /**
	   * Reads the state tree managed by the store.
	   *
	   * @returns {any} The current state tree of your application.
	   */
	  function getState() {
	    return currentState;
	  }

	  /**
	   * Adds a change listener. It will be called any time an action is dispatched,
	   * and some part of the state tree may potentially have changed. You may then
	   * call `getState()` to read the current state tree inside the callback.
	   *
	   * You may call `dispatch()` from a change listener, with the following
	   * caveats:
	   *
	   * 1. The subscriptions are snapshotted just before every `dispatch()` call.
	   * If you subscribe or unsubscribe while the listeners are being invoked, this
	   * will not have any effect on the `dispatch()` that is currently in progress.
	   * However, the next `dispatch()` call, whether nested or not, will use a more
	   * recent snapshot of the subscription list.
	   *
	   * 2. The listener should not expect to see all state changes, as the state
	   * might have been updated multiple times during a nested `dispatch()` before
	   * the listener is called. It is, however, guaranteed that all subscribers
	   * registered before the `dispatch()` started will be called with the latest
	   * state by the time it exits.
	   *
	   * @param {Function} listener A callback to be invoked on every dispatch.
	   * @returns {Function} A function to remove this change listener.
	   */
	  function subscribe(listener) {
	    if (typeof listener !== 'function') {
	      throw new Error('Expected listener to be a function.');
	    }

	    var isSubscribed = true;

	    ensureCanMutateNextListeners();
	    nextListeners.push(listener);

	    return function unsubscribe() {
	      if (!isSubscribed) {
	        return;
	      }

	      isSubscribed = false;

	      ensureCanMutateNextListeners();
	      var index = nextListeners.indexOf(listener);
	      nextListeners.splice(index, 1);
	    };
	  }

	  /**
	   * Dispatches an action. It is the only way to trigger a state change.
	   *
	   * The `reducer` function, used to create the store, will be called with the
	   * current state tree and the given `action`. Its return value will
	   * be considered the **next** state of the tree, and the change listeners
	   * will be notified.
	   *
	   * The base implementation only supports plain object actions. If you want to
	   * dispatch a Promise, an Observable, a thunk, or something else, you need to
	   * wrap your store creating function into the corresponding middleware. For
	   * example, see the documentation for the `redux-thunk` package. Even the
	   * middleware will eventually dispatch plain object actions using this method.
	   *
	   * @param {Object} action A plain object representing “what changed”. It is
	   * a good idea to keep actions serializable so you can record and replay user
	   * sessions, or use the time travelling `redux-devtools`. An action must have
	   * a `type` property which may not be `undefined`. It is a good idea to use
	   * string constants for action types.
	   *
	   * @returns {Object} For convenience, the same action object you dispatched.
	   *
	   * Note that, if you use a custom middleware, it may wrap `dispatch()` to
	   * return something else (for example, a Promise you can await).
	   */
	  function dispatch(action) {
	    if (!(0, _isPlainObject2['default'])(action)) {
	      throw new Error('Actions must be plain objects. ' + 'Use custom middleware for async actions.');
	    }

	    if (typeof action.type === 'undefined') {
	      throw new Error('Actions may not have an undefined "type" property. ' + 'Have you misspelled a constant?');
	    }

	    if (isDispatching) {
	      throw new Error('Reducers may not dispatch actions.');
	    }

	    try {
	      isDispatching = true;
	      currentState = currentReducer(currentState, action);
	    } finally {
	      isDispatching = false;
	    }

	    var listeners = currentListeners = nextListeners;
	    for (var i = 0; i < listeners.length; i++) {
	      var listener = listeners[i];
	      listener();
	    }

	    return action;
	  }

	  /**
	   * Replaces the reducer currently used by the store to calculate the state.
	   *
	   * You might need this if your app implements code splitting and you want to
	   * load some of the reducers dynamically. You might also need this if you
	   * implement a hot reloading mechanism for Redux.
	   *
	   * @param {Function} nextReducer The reducer for the store to use instead.
	   * @returns {void}
	   */
	  function replaceReducer(nextReducer) {
	    if (typeof nextReducer !== 'function') {
	      throw new Error('Expected the nextReducer to be a function.');
	    }

	    currentReducer = nextReducer;
	    dispatch({ type: ActionTypes.INIT });
	  }

	  /**
	   * Interoperability point for observable/reactive libraries.
	   * @returns {observable} A minimal observable of state changes.
	   * For more information, see the observable proposal:
	   * https://github.com/tc39/proposal-observable
	   */
	  function observable() {
	    var _ref;

	    var outerSubscribe = subscribe;
	    return _ref = {
	      /**
	       * The minimal observable subscription method.
	       * @param {Object} observer Any object that can be used as an observer.
	       * The observer object should have a `next` method.
	       * @returns {subscription} An object with an `unsubscribe` method that can
	       * be used to unsubscribe the observable from the store, and prevent further
	       * emission of values from the observable.
	       */
	      subscribe: function subscribe(observer) {
	        if (typeof observer !== 'object') {
	          throw new TypeError('Expected the observer to be an object.');
	        }

	        function observeState() {
	          if (observer.next) {
	            observer.next(getState());
	          }
	        }

	        observeState();
	        var unsubscribe = outerSubscribe(observeState);
	        return { unsubscribe: unsubscribe };
	      }
	    }, _ref[_symbolObservable2['default']] = function () {
	      return this;
	    }, _ref;
	  }

	  // When a store is created, an "INIT" action is dispatched so that every
	  // reducer returns their initial state. This effectively populates
	  // the initial state tree.
	  dispatch({ type: ActionTypes.INIT });

	  return _ref2 = {
	    dispatch: dispatch,
	    subscribe: subscribe,
	    getState: getState,
	    replaceReducer: replaceReducer
	  }, _ref2[_symbolObservable2['default']] = observable, _ref2;
	}

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

	var baseGetTag = __webpack_require__(8),
	    getPrototype = __webpack_require__(14),
	    isObjectLike = __webpack_require__(16);

	/** `Object#toString` result references. */
	var objectTag = '[object Object]';

	/** Used for built-in method references. */
	var funcProto = Function.prototype,
	    objectProto = Object.prototype;

	/** Used to resolve the decompiled source of functions. */
	var funcToString = funcProto.toString;

	/** Used to check objects for own properties. */
	var hasOwnProperty = objectProto.hasOwnProperty;

	/** Used to infer the `Object` constructor. */
	var objectCtorString = funcToString.call(Object);

	/**
	 * Checks if `value` is a plain object, that is, an object created by the
	 * `Object` constructor or one with a `[[Prototype]]` of `null`.
	 *
	 * @static
	 * @memberOf _
	 * @since 0.8.0
	 * @category Lang
	 * @param {*} value The value to check.
	 * @returns {boolean} Returns `true` if `value` is a plain object, else `false`.
	 * @example
	 *
	 * function Foo() {
	 *   this.a = 1;
	 * }
	 *
	 * _.isPlainObject(new Foo);
	 * // => false
	 *
	 * _.isPlainObject([1, 2, 3]);
	 * // => false
	 *
	 * _.isPlainObject({ 'x': 0, 'y': 0 });
	 * // => true
	 *
	 * _.isPlainObject(Object.create(null));
	 * // => true
	 */
	function isPlainObject(value) {
	  if (!isObjectLike(value) || baseGetTag(value) != objectTag) {
	    return false;
	  }
	  var proto = getPrototype(value);
	  if (proto === null) {
	    return true;
	  }
	  var Ctor = hasOwnProperty.call(proto, 'constructor') && proto.constructor;
	  return typeof Ctor == 'function' && Ctor instanceof Ctor &&
	    funcToString.call(Ctor) == objectCtorString;
	}

	module.exports = isPlainObject;


/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

	var Symbol = __webpack_require__(9),
	    getRawTag = __webpack_require__(12),
	    objectToString = __webpack_require__(13);

	/** `Object#toString` result references. */
	var nullTag = '[object Null]',
	    undefinedTag = '[object Undefined]';

	/** Built-in value references. */
	var symToStringTag = Symbol ? Symbol.toStringTag : undefined;

	/**
	 * The base implementation of `getTag` without fallbacks for buggy environments.
	 *
	 * @private
	 * @param {*} value The value to query.
	 * @returns {string} Returns the `toStringTag`.
	 */
	function baseGetTag(value) {
	  if (value == null) {
	    return value === undefined ? undefinedTag : nullTag;
	  }
	  return (symToStringTag && symToStringTag in Object(value))
	    ? getRawTag(value)
	    : objectToString(value);
	}

	module.exports = baseGetTag;


/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

	var root = __webpack_require__(10);

	/** Built-in value references. */
	var Symbol = root.Symbol;

	module.exports = Symbol;


/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

	var freeGlobal = __webpack_require__(11);

	/** Detect free variable `self`. */
	var freeSelf = typeof self == 'object' && self && self.Object === Object && self;

	/** Used as a reference to the global object. */
	var root = freeGlobal || freeSelf || Function('return this')();

	module.exports = root;


/***/ }),
/* 11 */
/***/ (function(module, exports) {

	/* WEBPACK VAR INJECTION */(function(global) {/** Detect free variable `global` from Node.js. */
	var freeGlobal = typeof global == 'object' && global && global.Object === Object && global;

	module.exports = freeGlobal;

	/* WEBPACK VAR INJECTION */}.call(exports, (function() { return this; }())))

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

	var Symbol = __webpack_require__(9);

	/** Used for built-in method references. */
	var objectProto = Object.prototype;

	/** Used to check objects for own properties. */
	var hasOwnProperty = objectProto.hasOwnProperty;

	/**
	 * Used to resolve the
	 * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
	 * of values.
	 */
	var nativeObjectToString = objectProto.toString;

	/** Built-in value references. */
	var symToStringTag = Symbol ? Symbol.toStringTag : undefined;

	/**
	 * A specialized version of `baseGetTag` which ignores `Symbol.toStringTag` values.
	 *
	 * @private
	 * @param {*} value The value to query.
	 * @returns {string} Returns the raw `toStringTag`.
	 */
	function getRawTag(value) {
	  var isOwn = hasOwnProperty.call(value, symToStringTag),
	      tag = value[symToStringTag];

	  try {
	    value[symToStringTag] = undefined;
	    var unmasked = true;
	  } catch (e) {}

	  var result = nativeObjectToString.call(value);
	  if (unmasked) {
	    if (isOwn) {
	      value[symToStringTag] = tag;
	    } else {
	      delete value[symToStringTag];
	    }
	  }
	  return result;
	}

	module.exports = getRawTag;


/***/ }),
/* 13 */
/***/ (function(module, exports) {

	/** Used for built-in method references. */
	var objectProto = Object.prototype;

	/**
	 * Used to resolve the
	 * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
	 * of values.
	 */
	var nativeObjectToString = objectProto.toString;

	/**
	 * Converts `value` to a string using `Object.prototype.toString`.
	 *
	 * @private
	 * @param {*} value The value to convert.
	 * @returns {string} Returns the converted string.
	 */
	function objectToString(value) {
	  return nativeObjectToString.call(value);
	}

	module.exports = objectToString;


/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

	var overArg = __webpack_require__(15);

	/** Built-in value references. */
	var getPrototype = overArg(Object.getPrototypeOf, Object);

	module.exports = getPrototype;


/***/ }),
/* 15 */
/***/ (function(module, exports) {

	/**
	 * Creates a unary function that invokes `func` with its argument transformed.
	 *
	 * @private
	 * @param {Function} func The function to wrap.
	 * @param {Function} transform The argument transform.
	 * @returns {Function} Returns the new function.
	 */
	function overArg(func, transform) {
	  return function(arg) {
	    return func(transform(arg));
	  };
	}

	module.exports = overArg;


/***/ }),
/* 16 */
/***/ (function(module, exports) {

	/**
	 * Checks if `value` is object-like. A value is object-like if it's not `null`
	 * and has a `typeof` result of "object".
	 *
	 * @static
	 * @memberOf _
	 * @since 4.0.0
	 * @category Lang
	 * @param {*} value The value to check.
	 * @returns {boolean} Returns `true` if `value` is object-like, else `false`.
	 * @example
	 *
	 * _.isObjectLike({});
	 * // => true
	 *
	 * _.isObjectLike([1, 2, 3]);
	 * // => true
	 *
	 * _.isObjectLike(_.noop);
	 * // => false
	 *
	 * _.isObjectLike(null);
	 * // => false
	 */
	function isObjectLike(value) {
	  return value != null && typeof value == 'object';
	}

	module.exports = isObjectLike;


/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

	module.exports = __webpack_require__(18);


/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function(global, module) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});

	var _ponyfill = __webpack_require__(20);

	var _ponyfill2 = _interopRequireDefault(_ponyfill);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

	var root; /* global window */


	if (typeof self !== 'undefined') {
	  root = self;
	} else if (typeof window !== 'undefined') {
	  root = window;
	} else if (typeof global !== 'undefined') {
	  root = global;
	} else if (true) {
	  root = module;
	} else {
	  root = Function('return this')();
	}

	var result = (0, _ponyfill2['default'])(root);
	exports['default'] = result;
	/* WEBPACK VAR INJECTION */}.call(exports, (function() { return this; }()), __webpack_require__(19)(module)))

/***/ }),
/* 19 */
/***/ (function(module, exports) {

	module.exports = function(module) {
		if(!module.webpackPolyfill) {
			module.deprecate = function() {};
			module.paths = [];
			// module.parent = undefined by default
			module.children = [];
			module.webpackPolyfill = 1;
		}
		return module;
	}


/***/ }),
/* 20 */
/***/ (function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	exports['default'] = symbolObservablePonyfill;
	function symbolObservablePonyfill(root) {
		var result;
		var _Symbol = root.Symbol;

		if (typeof _Symbol === 'function') {
			if (_Symbol.observable) {
				result = _Symbol.observable;
			} else {
				result = _Symbol('observable');
				_Symbol.observable = result;
			}
		} else {
			result = '@@observable';
		}

		return result;
	};

/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;
	exports['default'] = combineReducers;

	var _createStore = __webpack_require__(6);

	var _isPlainObject = __webpack_require__(7);

	var _isPlainObject2 = _interopRequireDefault(_isPlainObject);

	var _warning = __webpack_require__(22);

	var _warning2 = _interopRequireDefault(_warning);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

	function getUndefinedStateErrorMessage(key, action) {
	  var actionType = action && action.type;
	  var actionName = actionType && '"' + actionType.toString() + '"' || 'an action';

	  return 'Given action ' + actionName + ', reducer "' + key + '" returned undefined. ' + 'To ignore an action, you must explicitly return the previous state. ' + 'If you want this reducer to hold no value, you can return null instead of undefined.';
	}

	function getUnexpectedStateShapeWarningMessage(inputState, reducers, action, unexpectedKeyCache) {
	  var reducerKeys = Object.keys(reducers);
	  var argumentName = action && action.type === _createStore.ActionTypes.INIT ? 'preloadedState argument passed to createStore' : 'previous state received by the reducer';

	  if (reducerKeys.length === 0) {
	    return 'Store does not have a valid reducer. Make sure the argument passed ' + 'to combineReducers is an object whose values are reducers.';
	  }

	  if (!(0, _isPlainObject2['default'])(inputState)) {
	    return 'The ' + argumentName + ' has unexpected type of "' + {}.toString.call(inputState).match(/\s([a-z|A-Z]+)/)[1] + '". Expected argument to be an object with the following ' + ('keys: "' + reducerKeys.join('", "') + '"');
	  }

	  var unexpectedKeys = Object.keys(inputState).filter(function (key) {
	    return !reducers.hasOwnProperty(key) && !unexpectedKeyCache[key];
	  });

	  unexpectedKeys.forEach(function (key) {
	    unexpectedKeyCache[key] = true;
	  });

	  if (unexpectedKeys.length > 0) {
	    return 'Unexpected ' + (unexpectedKeys.length > 1 ? 'keys' : 'key') + ' ' + ('"' + unexpectedKeys.join('", "') + '" found in ' + argumentName + '. ') + 'Expected to find one of the known reducer keys instead: ' + ('"' + reducerKeys.join('", "') + '". Unexpected keys will be ignored.');
	  }
	}

	function assertReducerShape(reducers) {
	  Object.keys(reducers).forEach(function (key) {
	    var reducer = reducers[key];
	    var initialState = reducer(undefined, { type: _createStore.ActionTypes.INIT });

	    if (typeof initialState === 'undefined') {
	      throw new Error('Reducer "' + key + '" returned undefined during initialization. ' + 'If the state passed to the reducer is undefined, you must ' + 'explicitly return the initial state. The initial state may ' + 'not be undefined. If you don\'t want to set a value for this reducer, ' + 'you can use null instead of undefined.');
	    }

	    var type = '@@redux/PROBE_UNKNOWN_ACTION_' + Math.random().toString(36).substring(7).split('').join('.');
	    if (typeof reducer(undefined, { type: type }) === 'undefined') {
	      throw new Error('Reducer "' + key + '" returned undefined when probed with a random type. ' + ('Don\'t try to handle ' + _createStore.ActionTypes.INIT + ' or other actions in "redux/*" ') + 'namespace. They are considered private. Instead, you must return the ' + 'current state for any unknown actions, unless it is undefined, ' + 'in which case you must return the initial state, regardless of the ' + 'action type. The initial state may not be undefined, but can be null.');
	    }
	  });
	}

	/**
	 * Turns an object whose values are different reducer functions, into a single
	 * reducer function. It will call every child reducer, and gather their results
	 * into a single state object, whose keys correspond to the keys of the passed
	 * reducer functions.
	 *
	 * @param {Object} reducers An object whose values correspond to different
	 * reducer functions that need to be combined into one. One handy way to obtain
	 * it is to use ES6 `import * as reducers` syntax. The reducers may never return
	 * undefined for any action. Instead, they should return their initial state
	 * if the state passed to them was undefined, and the current state for any
	 * unrecognized action.
	 *
	 * @returns {Function} A reducer function that invokes every reducer inside the
	 * passed object, and builds a state object with the same shape.
	 */
	function combineReducers(reducers) {
	  var reducerKeys = Object.keys(reducers);
	  var finalReducers = {};
	  for (var i = 0; i < reducerKeys.length; i++) {
	    var key = reducerKeys[i];

	    if (false) {
	      if (typeof reducers[key] === 'undefined') {
	        (0, _warning2['default'])('No reducer provided for key "' + key + '"');
	      }
	    }

	    if (typeof reducers[key] === 'function') {
	      finalReducers[key] = reducers[key];
	    }
	  }
	  var finalReducerKeys = Object.keys(finalReducers);

	  var unexpectedKeyCache = void 0;
	  if (false) {
	    unexpectedKeyCache = {};
	  }

	  var shapeAssertionError = void 0;
	  try {
	    assertReducerShape(finalReducers);
	  } catch (e) {
	    shapeAssertionError = e;
	  }

	  return function combination() {
	    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var action = arguments[1];

	    if (shapeAssertionError) {
	      throw shapeAssertionError;
	    }

	    if (false) {
	      var warningMessage = getUnexpectedStateShapeWarningMessage(state, finalReducers, action, unexpectedKeyCache);
	      if (warningMessage) {
	        (0, _warning2['default'])(warningMessage);
	      }
	    }

	    var hasChanged = false;
	    var nextState = {};
	    for (var _i = 0; _i < finalReducerKeys.length; _i++) {
	      var _key = finalReducerKeys[_i];
	      var reducer = finalReducers[_key];
	      var previousStateForKey = state[_key];
	      var nextStateForKey = reducer(previousStateForKey, action);
	      if (typeof nextStateForKey === 'undefined') {
	        var errorMessage = getUndefinedStateErrorMessage(_key, action);
	        throw new Error(errorMessage);
	      }
	      nextState[_key] = nextStateForKey;
	      hasChanged = hasChanged || nextStateForKey !== previousStateForKey;
	    }
	    return hasChanged ? nextState : state;
	  };
	}

/***/ }),
/* 22 */
/***/ (function(module, exports) {

	'use strict';

	exports.__esModule = true;
	exports['default'] = warning;
	/**
	 * Prints a warning in the console if it exists.
	 *
	 * @param {String} message The warning message.
	 * @returns {void}
	 */
	function warning(message) {
	  /* eslint-disable no-console */
	  if (typeof console !== 'undefined' && typeof console.error === 'function') {
	    console.error(message);
	  }
	  /* eslint-enable no-console */
	  try {
	    // This error was thrown as a convenience so that if you enable
	    // "break on all exceptions" in your console,
	    // it would pause the execution at this line.
	    throw new Error(message);
	    /* eslint-disable no-empty */
	  } catch (e) {}
	  /* eslint-enable no-empty */
	}

/***/ }),
/* 23 */
/***/ (function(module, exports) {

	'use strict';

	exports.__esModule = true;
	exports['default'] = bindActionCreators;
	function bindActionCreator(actionCreator, dispatch) {
	  return function () {
	    return dispatch(actionCreator.apply(undefined, arguments));
	  };
	}

	/**
	 * Turns an object whose values are action creators, into an object with the
	 * same keys, but with every function wrapped into a `dispatch` call so they
	 * may be invoked directly. This is just a convenience method, as you can call
	 * `store.dispatch(MyActionCreators.doSomething())` yourself just fine.
	 *
	 * For convenience, you can also pass a single function as the first argument,
	 * and get a function in return.
	 *
	 * @param {Function|Object} actionCreators An object whose values are action
	 * creator functions. One handy way to obtain it is to use ES6 `import * as`
	 * syntax. You may also pass a single function.
	 *
	 * @param {Function} dispatch The `dispatch` function available on your Redux
	 * store.
	 *
	 * @returns {Function|Object} The object mimicking the original object, but with
	 * every action creator wrapped into the `dispatch` call. If you passed a
	 * function as `actionCreators`, the return value will also be a single
	 * function.
	 */
	function bindActionCreators(actionCreators, dispatch) {
	  if (typeof actionCreators === 'function') {
	    return bindActionCreator(actionCreators, dispatch);
	  }

	  if (typeof actionCreators !== 'object' || actionCreators === null) {
	    throw new Error('bindActionCreators expected an object or a function, instead received ' + (actionCreators === null ? 'null' : typeof actionCreators) + '. ' + 'Did you write "import ActionCreators from" instead of "import * as ActionCreators from"?');
	  }

	  var keys = Object.keys(actionCreators);
	  var boundActionCreators = {};
	  for (var i = 0; i < keys.length; i++) {
	    var key = keys[i];
	    var actionCreator = actionCreators[key];
	    if (typeof actionCreator === 'function') {
	      boundActionCreators[key] = bindActionCreator(actionCreator, dispatch);
	    }
	  }
	  return boundActionCreators;
	}

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;

	var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

	exports['default'] = applyMiddleware;

	var _compose = __webpack_require__(25);

	var _compose2 = _interopRequireDefault(_compose);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

	/**
	 * Creates a store enhancer that applies middleware to the dispatch method
	 * of the Redux store. This is handy for a variety of tasks, such as expressing
	 * asynchronous actions in a concise manner, or logging every action payload.
	 *
	 * See `redux-thunk` package as an example of the Redux middleware.
	 *
	 * Because middleware is potentially asynchronous, this should be the first
	 * store enhancer in the composition chain.
	 *
	 * Note that each middleware will be given the `dispatch` and `getState` functions
	 * as named arguments.
	 *
	 * @param {...Function} middlewares The middleware chain to be applied.
	 * @returns {Function} A store enhancer applying the middleware.
	 */
	function applyMiddleware() {
	  for (var _len = arguments.length, middlewares = Array(_len), _key = 0; _key < _len; _key++) {
	    middlewares[_key] = arguments[_key];
	  }

	  return function (createStore) {
	    return function (reducer, preloadedState, enhancer) {
	      var store = createStore(reducer, preloadedState, enhancer);
	      var _dispatch = store.dispatch;
	      var chain = [];

	      var middlewareAPI = {
	        getState: store.getState,
	        dispatch: function dispatch(action) {
	          return _dispatch(action);
	        }
	      };
	      chain = middlewares.map(function (middleware) {
	        return middleware(middlewareAPI);
	      });
	      _dispatch = _compose2['default'].apply(undefined, chain)(store.dispatch);

	      return _extends({}, store, {
	        dispatch: _dispatch
	      });
	    };
	  };
	}

/***/ }),
/* 25 */
/***/ (function(module, exports) {

	"use strict";

	exports.__esModule = true;
	exports["default"] = compose;
	/**
	 * Composes single-argument functions from right to left. The rightmost
	 * function can take multiple arguments as it provides the signature for
	 * the resulting composite function.
	 *
	 * @param {...Function} funcs The functions to compose.
	 * @returns {Function} A function obtained by composing the argument functions
	 * from right to left. For example, compose(f, g, h) is identical to doing
	 * (...args) => f(g(h(...args))).
	 */

	function compose() {
	  for (var _len = arguments.length, funcs = Array(_len), _key = 0; _key < _len; _key++) {
	    funcs[_key] = arguments[_key];
	  }

	  if (funcs.length === 0) {
	    return function (arg) {
	      return arg;
	    };
	  }

	  if (funcs.length === 1) {
	    return funcs[0];
	  }

	  return funcs.reduce(function (a, b) {
	    return function () {
	      return a(b.apply(undefined, arguments));
	    };
	  });
	}

/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});

	var _redux = __webpack_require__(5);

	var _items = __webpack_require__(27);

	var _items2 = _interopRequireDefault(_items);

	var _groups = __webpack_require__(28);

	var _groups2 = _interopRequireDefault(_groups);

	var _choices = __webpack_require__(29);

	var _choices2 = _interopRequireDefault(_choices);

	var _general = __webpack_require__(30);

	var _general2 = _interopRequireDefault(_general);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	var appReducer = (0, _redux.combineReducers)({
	  items: _items2.default,
	  groups: _groups2.default,
	  choices: _choices2.default,
	  general: _general2.default
	});

	var rootReducer = function rootReducer(passedState, action) {
	  var state = passedState;
	  // If we are clearing all items, groups and options we reassign
	  // state and then pass that state to our proper reducer. This isn't
	  // mutating our actual state
	  // See: http://stackoverflow.com/a/35641992
	  if (action.type === 'CLEAR_ALL') {
	    state = undefined;
	  }

	  return appReducer(state, action);
	};

	exports.default = rootReducer;

/***/ }),
/* 27 */
/***/ (function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	var items = function items() {
	  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	  var action = arguments[1];

	  switch (action.type) {
	    case 'ADD_ITEM':
	      {
	        // Add object to items array
	        var newState = [].concat(_toConsumableArray(state), [{
	          id: action.id,
	          choiceId: action.choiceId,
	          groupId: action.groupId,
	          value: action.value,
	          label: action.label,
	          active: true,
	          highlighted: false,
	          customProperties: action.customProperties,
	          placeholder: action.placeholder || false,
	          keyCode: null
	        }]);

	        return newState.map(function (item) {
	          if (item.highlighted) {
	            item.highlighted = false;
	          }
	          return item;
	        });
	      }

	    case 'REMOVE_ITEM':
	      {
	        // Set item to inactive
	        return state.map(function (item) {
	          if (item.id === action.id) {
	            item.active = false;
	          }
	          return item;
	        });
	      }

	    case 'HIGHLIGHT_ITEM':
	      {
	        return state.map(function (item) {
	          if (item.id === action.id) {
	            item.highlighted = action.highlighted;
	          }
	          return item;
	        });
	      }

	    default:
	      {
	        return state;
	      }
	  }
	};

	exports.default = items;

/***/ }),
/* 28 */
/***/ (function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	var groups = function groups() {
	  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	  var action = arguments[1];

	  switch (action.type) {
	    case 'ADD_GROUP':
	      {
	        return [].concat(_toConsumableArray(state), [{
	          id: action.id,
	          value: action.value,
	          active: action.active,
	          disabled: action.disabled
	        }]);
	      }

	    case 'CLEAR_CHOICES':
	      {
	        return state.groups = [];
	      }

	    default:
	      {
	        return state;
	      }
	  }
	};

	exports.default = groups;

/***/ }),
/* 29 */
/***/ (function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	var choices = function choices() {
	  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	  var action = arguments[1];

	  switch (action.type) {
	    case 'ADD_CHOICE':
	      {
	        /*
	            A disabled choice appears in the choice dropdown but cannot be selected
	            A selected choice has been added to the passed input's value (added as an item)
	            An active choice appears within the choice dropdown
	         */
	        return [].concat(_toConsumableArray(state), [{
	          id: action.id,
	          elementId: action.elementId,
	          groupId: action.groupId,
	          value: action.value,
	          label: action.label || action.value,
	          disabled: action.disabled || false,
	          selected: false,
	          active: true,
	          score: 9999,
	          customProperties: action.customProperties,
	          placeholder: action.placeholder || false,
	          keyCode: null
	        }]);
	      }

	    case 'ADD_ITEM':
	      {
	        var newState = state;

	        // If all choices need to be activated
	        if (action.activateOptions) {
	          newState = state.map(function (choice) {
	            choice.active = action.active;
	            return choice;
	          });
	        }
	        // When an item is added and it has an associated choice,
	        // we want to disable it so it can't be chosen again
	        if (action.choiceId > -1) {
	          newState = state.map(function (choice) {
	            if (choice.id === parseInt(action.choiceId, 10)) {
	              choice.selected = true;
	            }
	            return choice;
	          });
	        }

	        return newState;
	      }

	    case 'REMOVE_ITEM':
	      {
	        // When an item is removed and it has an associated choice,
	        // we want to re-enable it so it can be chosen again
	        if (action.choiceId > -1) {
	          return state.map(function (choice) {
	            if (choice.id === parseInt(action.choiceId, 10)) {
	              choice.selected = false;
	            }
	            return choice;
	          });
	        }

	        return state;
	      }

	    case 'FILTER_CHOICES':
	      {
	        var filteredResults = action.results;
	        var filteredState = state.map(function (choice) {
	          // Set active state based on whether choice is
	          // within filtered results

	          choice.active = filteredResults.some(function (result) {
	            if (result.item.id === choice.id) {
	              choice.score = result.score;
	              return true;
	            }
	            return false;
	          });

	          return choice;
	        });

	        return filteredState;
	      }

	    case 'ACTIVATE_CHOICES':
	      {
	        return state.map(function (choice) {
	          choice.active = action.active;
	          return choice;
	        });
	      }

	    case 'CLEAR_CHOICES':
	      {
	        return state.choices = [];
	      }

	    default:
	      {
	        return state;
	      }
	  }
	};

	exports.default = choices;

/***/ }),
/* 30 */
/***/ (function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var general = function general() {
	  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : { loading: false };
	  var action = arguments[1];

	  switch (action.type) {
	    case 'LOADING':
	      {
	        return {
	          loading: action.isLoading
	        };
	      }

	    default:
	      {
	        return state;
	      }
	  }
	};

	exports.default = general;

/***/ }),
/* 31 */
/***/ (function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var addItem = exports.addItem = function addItem(value, label, id, choiceId, groupId, customProperties, placeholder, keyCode) {
	  return {
	    type: 'ADD_ITEM',
	    value: value,
	    label: label,
	    id: id,
	    choiceId: choiceId,
	    groupId: groupId,
	    customProperties: customProperties,
	    placeholder: placeholder,
	    keyCode: keyCode
	  };
	};

	var removeItem = exports.removeItem = function removeItem(id, choiceId) {
	  return {
	    type: 'REMOVE_ITEM',
	    id: id,
	    choiceId: choiceId
	  };
	};

	var highlightItem = exports.highlightItem = function highlightItem(id, highlighted) {
	  return {
	    type: 'HIGHLIGHT_ITEM',
	    id: id,
	    highlighted: highlighted
	  };
	};

	var addChoice = exports.addChoice = function addChoice(value, label, id, groupId, disabled, elementId, customProperties, placeholder, keyCode) {
	  return {
	    type: 'ADD_CHOICE',
	    value: value,
	    label: label,
	    id: id,
	    groupId: groupId,
	    disabled: disabled,
	    elementId: elementId,
	    customProperties: customProperties,
	    placeholder: placeholder,
	    keyCode: keyCode
	  };
	};

	var filterChoices = exports.filterChoices = function filterChoices(results) {
	  return {
	    type: 'FILTER_CHOICES',
	    results: results
	  };
	};

	var activateChoices = exports.activateChoices = function activateChoices() {
	  var active = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	  return {
	    type: 'ACTIVATE_CHOICES',
	    active: active
	  };
	};

	var clearChoices = exports.clearChoices = function clearChoices() {
	  return {
	    type: 'CLEAR_CHOICES'
	  };
	};

	var addGroup = exports.addGroup = function addGroup(value, id, active, disabled) {
	  return {
	    type: 'ADD_GROUP',
	    value: value,
	    id: id,
	    active: active,
	    disabled: disabled
	  };
	};

	var clearAll = exports.clearAll = function clearAll() {
	  return {
	    type: 'CLEAR_ALL'
	  };
	};

	var setIsLoading = exports.setIsLoading = function setIsLoading(isLoading) {
	  return {
	    type: 'LOADING',
	    isLoading: isLoading
	  };
		};

/***/ }),
/* 32 */
/***/ (function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});

	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

	/* eslint-disable */
	/**
	 * Capitalises the first letter of each word in a string
	 * @param  {String} str String to capitalise
	 * @return {String}     Capitalised string
	 */
	var capitalise = exports.capitalise = function capitalise(str) {
	  return str.replace(/\w\S*/g, function (txt) {
	    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
	  });
	};

	/**
	 * Generates a string of random chars
	 * @param  {Number} length Length of the string to generate
	 * @return {String} String of random chars
	 */
	var generateChars = exports.generateChars = function generateChars(length) {
	  var chars = '';

	  for (var i = 0; i < length; i++) {
	    var randomChar = getRandomNumber(0, 36);
	    chars += randomChar.toString(36);
	  }

	  return chars;
	};

	/**
	 * Generates a unique id based on an element
	 * @param  {HTMLElement} element Element to generate the id from
	 * @param  {String} Prefix for the Id
	 * @return {String} Unique Id
	 */
	var generateId = exports.generateId = function generateId(element, prefix) {
	  var id = element.id || element.name && element.name + '-' + generateChars(2) || generateChars(4);
	  id = id.replace(/(:|\.|\[|\]|,)/g, '');
	  id = prefix + id;

	  return id;
	};

	/**
	 * Tests the type of an object
	 * @param  {String}  type Type to test object against
	 * @param  {Object}  obj  Object to be tested
	 * @return {Boolean}
	 */
	var getType = exports.getType = function getType(obj) {
	  return Object.prototype.toString.call(obj).slice(8, -1);
	};

	/**
	 * Tests the type of an object
	 * @param  {String}  type Type to test object against
	 * @param  {Object}  obj  Object to be tested
	 * @return {Boolean}
	 */
	var isType = exports.isType = function isType(type, obj) {
	  var clas = getType(obj);
	  return obj !== undefined && obj !== null && clas === type;
	};

	/**
	 * Tests to see if a passed object is a node
	 * @param  {Object}  obj  Object to be tested
	 * @return {Boolean}
	 */
	var isNode = exports.isNode = function isNode(o) {
	  return (typeof Node === 'undefined' ? 'undefined' : _typeof(Node)) === "object" ? o instanceof Node : o && (typeof o === 'undefined' ? 'undefined' : _typeof(o)) === "object" && typeof o.nodeType === "number" && typeof o.nodeName === "string";
	};

	/**
	 * Tests to see if a passed object is an element
	 * @param  {Object}  obj  Object to be tested
	 * @return {Boolean}
	 */
	var isElement = exports.isElement = function isElement(o) {
	  return (typeof HTMLElement === 'undefined' ? 'undefined' : _typeof(HTMLElement)) === "object" ? o instanceof HTMLElement : //DOM2
	  o && (typeof o === 'undefined' ? 'undefined' : _typeof(o)) === "object" && o !== null && o.nodeType === 1 && typeof o.nodeName === "string";
	};

	/**
	 * Merges unspecified amount of objects into new object
	 * @private
	 * @return {Object} Merged object of arguments
	 */
	var extend = exports.extend = function extend() {
	  var extended = {};
	  var length = arguments.length;

	  /**
	   * Merge one object into another
	   * @param  {Object} obj  Object to merge into extended object
	   */
	  var merge = function merge(obj) {
	    for (var prop in obj) {
	      if (Object.prototype.hasOwnProperty.call(obj, prop)) {
	        // If deep merge and property is an object, merge properties
	        if (isType('Object', obj[prop])) {
	          extended[prop] = extend(true, extended[prop], obj[prop]);
	        } else {
	          extended[prop] = obj[prop];
	        }
	      }
	    }
	  };

	  // Loop through each passed argument
	  for (var i = 0; i < length; i++) {
	    // store argument at position i
	    var obj = arguments[i];

	    // If we are in fact dealing with an object, merge it.
	    if (isType('Object', obj)) {
	      merge(obj);
	    }
	  }

	  return extended;
	};

	/**
	 * CSS transition end event listener
	 * @return
	 */
	var whichTransitionEvent = exports.whichTransitionEvent = function whichTransitionEvent() {
	  var t,
	      el = document.createElement('fakeelement');

	  var transitions = {
	    'transition': 'transitionend',
	    'OTransition': 'oTransitionEnd',
	    'MozTransition': 'transitionend',
	    'WebkitTransition': 'webkitTransitionEnd'
	  };

	  for (t in transitions) {
	    if (el.style[t] !== undefined) {
	      return transitions[t];
	    }
	  }
	};

	/**
	 * CSS animation end event listener
	 * @return
	 */
	var whichAnimationEvent = exports.whichAnimationEvent = function whichAnimationEvent() {
	  var t,
	      el = document.createElement('fakeelement');

	  var animations = {
	    'animation': 'animationend',
	    'OAnimation': 'oAnimationEnd',
	    'MozAnimation': 'animationend',
	    'WebkitAnimation': 'webkitAnimationEnd'
	  };

	  for (t in animations) {
	    if (el.style[t] !== undefined) {
	      return animations[t];
	    }
	  }
	};

	/**
	 *  Get the ancestors of each element in the current set of matched elements,
	 *  up to but not including the element matched by the selector
	 * @param  {NodeElement} elem     Element to begin search from
	 * @param  {NodeElement} parent   Parent to find
	 * @param  {String} selector Class to find
	 * @return {Array}          Array of parent elements
	 */
	var getParentsUntil = exports.getParentsUntil = function getParentsUntil(elem, parent, selector) {
	  var parents = [];
	  // Get matches
	  for (; elem && elem !== document; elem = elem.parentNode) {

	    // Check if parent has been reached
	    if (parent) {

	      var parentType = parent.charAt(0);

	      // If parent is a class
	      if (parentType === '.') {
	        if (elem.classList.contains(parent.substr(1))) {
	          break;
	        }
	      }

	      // If parent is an ID
	      if (parentType === '#') {
	        if (elem.id === parent.substr(1)) {
	          break;
	        }
	      }

	      // If parent is a data attribute
	      if (parentType === '[') {
	        if (elem.hasAttribute(parent.substr(1, parent.length - 1))) {
	          break;
	        }
	      }

	      // If parent is a tag
	      if (elem.tagName.toLowerCase() === parent) {
	        break;
	      }
	    }
	    if (selector) {
	      var selectorType = selector.charAt(0);

	      // If selector is a class
	      if (selectorType === '.') {
	        if (elem.classList.contains(selector.substr(1))) {
	          parents.push(elem);
	        }
	      }

	      // If selector is an ID
	      if (selectorType === '#') {
	        if (elem.id === selector.substr(1)) {
	          parents.push(elem);
	        }
	      }

	      // If selector is a data attribute
	      if (selectorType === '[') {
	        if (elem.hasAttribute(selector.substr(1, selector.length - 1))) {
	          parents.push(elem);
	        }
	      }

	      // If selector is a tag
	      if (elem.tagName.toLowerCase() === selector) {
	        parents.push(elem);
	      }
	    } else {
	      parents.push(elem);
	    }
	  }

	  // Return parents if any exist
	  if (parents.length === 0) {
	    return null;
	  } else {
	    return parents;
	  }
	};

	var wrap = exports.wrap = function wrap(element, wrapper) {
	  wrapper = wrapper || document.createElement('div');
	  if (element.nextSibling) {
	    element.parentNode.insertBefore(wrapper, element.nextSibling);
	  } else {
	    element.parentNode.appendChild(wrapper);
	  }
	  return wrapper.appendChild(element);
	};

	var getSiblings = exports.getSiblings = function getSiblings(elem) {
	  var siblings = [];
	  var sibling = elem.parentNode.firstChild;
	  for (; sibling; sibling = sibling.nextSibling) {
	    if (sibling.nodeType === 1 && sibling !== elem) {
	      siblings.push(sibling);
	    }
	  }
	  return siblings;
	};

	/**
	 * Find ancestor in DOM tree
	 * @param  {NodeElement} el  Element to start search from
	 * @param  {[type]} cls Class of parent
	 * @return {NodeElement}     Found parent element
	 */
	var findAncestor = exports.findAncestor = function findAncestor(el, cls) {
	  while ((el = el.parentElement) && !el.classList.contains(cls)) {}
	  return el;
	};

	/**
	 * Find ancestor in DOM tree by attribute name
	 * @param  {NodeElement} el  Element to start search from
	 * @param  {string} attr Attribute name of parent
	 * @return {?NodeElement}     Found parent element or null
	 */
	var findAncestorByAttrName = exports.findAncestorByAttrName = function findAncestorByAttrName(el, attr) {
	  var target = el;

	  while (target) {
	    if (target.hasAttribute(attr)) {
	      return target;
	    }

	    target = target.parentElement;
	  }

	  return null;
	};

	/**
	 * Debounce an event handler.
	 * @param  {Function} func      Function to run after wait
	 * @param  {Number} wait      The delay before the function is executed
	 * @param  {Boolean} immediate  If  passed, trigger the function on the leading edge, instead of the trailing.
	 * @return {Function}           A function will be called after it stops being called for a given delay
	 */
	var debounce = exports.debounce = function debounce(func, wait, immediate) {
	  var timeout;
	  return function () {
	    var context = this,
	        args = arguments;
	    var later = function later() {
	      timeout = null;
	      if (!immediate) func.apply(context, args);
	    };
	    var callNow = immediate && !timeout;
	    clearTimeout(timeout);
	    timeout = setTimeout(later, wait);
	    if (callNow) func.apply(context, args);
	  };
	};

	/**
	 * Get an element's distance from the top of the page
	 * @private
	 * @param  {NodeElement} el Element to test for
	 * @return {Number} Elements Distance from top of page
	 */
	var getElemDistance = exports.getElemDistance = function getElemDistance(el) {
	  var location = 0;
	  if (el.offsetParent) {
	    do {
	      location += el.offsetTop;
	      el = el.offsetParent;
	    } while (el);
	  }
	  return location >= 0 ? location : 0;
	};

	/**
	 * Determine element height multiplied by any offsets
	 * @private
	 * @param  {HTMLElement} el Element to test for
	 * @return {Number}    Height of element
	 */
	var getElementOffset = exports.getElementOffset = function getElementOffset(el, offset) {
	  var elOffset = offset;
	  if (elOffset > 1) elOffset = 1;
	  if (elOffset > 0) elOffset = 0;

	  return Math.max(el.offsetHeight * elOffset);
	};

	/**
	 * Get the next or previous element from a given start point
	 * @param  {HTMLElement} startEl    Element to start position from
	 * @param  {String}      className  The class we will look through
	 * @param  {Number}      direction  Positive next element, negative previous element
	 * @return {[HTMLElement}           Found element
	 */
	var getAdjacentEl = exports.getAdjacentEl = function getAdjacentEl(startEl, className) {
	  var direction = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;

	  if (!startEl || !className) return;

	  var parent = startEl.parentNode.parentNode;
	  var children = Array.from(parent.querySelectorAll(className));

	  var startPos = children.indexOf(startEl);
	  var operatorDirection = direction > 0 ? 1 : -1;

	  return children[startPos + operatorDirection];
	};

	/**
	 * Get scroll position based on top/bottom position
	 * @private
	 * @return {String} Position of scroll
	 */
	var getScrollPosition = exports.getScrollPosition = function getScrollPosition(position) {
	  if (position === 'bottom') {
	    // Scroll position from the bottom of the viewport
	    return Math.max((window.scrollY || window.pageYOffset) + (window.innerHeight || document.documentElement.clientHeight));
	  } else {
	    // Scroll position from the top of the viewport
	    return window.scrollY || window.pageYOffset;
	  }
	};

	/**
	 * Determine whether an element is within the viewport
	 * @param  {HTMLElement}  el Element to test
	 * @return {String} Position of scroll
	 * @return {Boolean}
	 */
	var isInView = exports.isInView = function isInView(el, position, offset) {
	  // If the user has scrolled further than the distance from the element to the top of its parent
	  return this.getScrollPosition(position) > this.getElemDistance(el) + this.getElementOffset(el, offset) ? true : false;
	};

	/**
	 * Determine whether an element is within
	 * @param  {HTMLElement} el        Element to test
	 * @param  {HTMLElement} parent    Scrolling parent
	 * @param  {Number} direction      Whether element is visible from above or below
	 * @return {Boolean}
	 */
	var isScrolledIntoView = exports.isScrolledIntoView = function isScrolledIntoView(el, parent) {
	  var direction = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;

	  if (!el) return;

	  var isVisible = void 0;

	  if (direction > 0) {
	    // In view from bottom
	    isVisible = parent.scrollTop + parent.offsetHeight >= el.offsetTop + el.offsetHeight;
	  } else {
	    // In view from top
	    isVisible = el.offsetTop >= parent.scrollTop;
	  }

	  return isVisible;
	};

	/**
	 * Escape html in a string
	 * @param  {String} html  Initial string/html
	 * @return {String}  Sanitised string
	 */
	var stripHTML = exports.stripHTML = function stripHTML(html) {
	  return html.replace(/&/g, '&amp;').replace(/>/g, '&rt;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
	};

	/**
	 * Adds animation to an element and removes it upon animation completion
	 * @param  {Element} el        Element to add animation to
	 * @param  {String} animation Animation class to add to element
	 * @return
	 */
	var addAnimation = exports.addAnimation = function addAnimation(el, animation) {
	  var animationEvent = whichAnimationEvent();

	  var removeAnimation = function removeAnimation() {
	    el.classList.remove(animation);
	    el.removeEventListener(animationEvent, removeAnimation, false);
	  };

	  el.classList.add(animation);
	  el.addEventListener(animationEvent, removeAnimation, false);
	};

	/**
	 * Get a random number between a range
	 * @param  {Number} min Minimum range
	 * @param  {Number} max Maximum range
	 * @return {Number}     Random number
	 */
	var getRandomNumber = exports.getRandomNumber = function getRandomNumber(min, max) {
	  return Math.floor(Math.random() * (max - min) + min);
	};

	/**
	 * Turn a string into a node
	 * @param  {String} String to convert
	 * @return {HTMLElement}   Converted node element
	 */
	var strToEl = exports.strToEl = function () {
	  var tmpEl = document.createElement('div');
	  return function (str) {
	    var cleanedInput = str.trim();
	    var r = void 0;
	    tmpEl.innerHTML = cleanedInput;
	    r = tmpEl.children[0];

	    while (tmpEl.firstChild) {
	      tmpEl.removeChild(tmpEl.firstChild);
	    }

	    return r;
	  };
	}();

	/**
	 * Sets the width of a passed input based on its value
	 * @return {Number} Width of input
	 */
	var getWidthOfInput = exports.getWidthOfInput = function getWidthOfInput(input) {
	  var value = input.value || input.placeholder;
	  var width = input.offsetWidth;

	  if (value) {
	    var testEl = strToEl('<span>' + stripHTML(value) + '</span>');
	    testEl.style.position = 'absolute';
	    testEl.style.padding = '0';
	    testEl.style.top = '-9999px';
	    testEl.style.left = '-9999px';
	    testEl.style.width = 'auto';
	    testEl.style.whiteSpace = 'pre';

	    if (document.body.contains(input) && window.getComputedStyle) {
	      var inputStyle = window.getComputedStyle(input);

	      if (inputStyle) {
	        testEl.style.fontSize = inputStyle.fontSize;
	        testEl.style.fontFamily = inputStyle.fontFamily;
	        testEl.style.fontWeight = inputStyle.fontWeight;
	        testEl.style.fontStyle = inputStyle.fontStyle;
	        testEl.style.letterSpacing = inputStyle.letterSpacing;
	        testEl.style.textTransform = inputStyle.textTransform;
	        testEl.style.padding = inputStyle.padding;
	      }
	    }

	    document.body.appendChild(testEl);

	    if (value && testEl.offsetWidth !== input.offsetWidth) {
	      width = testEl.offsetWidth + 4;
	    }

	    document.body.removeChild(testEl);
	  }

	  return width + 'px';
	};

	/**
	 * Sorting function for current and previous string
	 * @param  {String} a Current value
	 * @param  {String} b Next value
	 * @return {Number}   -1 for after previous,
	 *                    1 for before,
	 *                    0 for same location
	 */
	var sortByAlpha = exports.sortByAlpha = function sortByAlpha(a, b) {
	  var labelA = (a.label || a.value).toLowerCase();
	  var labelB = (b.label || b.value).toLowerCase();

	  if (labelA < labelB) return -1;
	  if (labelA > labelB) return 1;
	  return 0;
	};

	/**
	 * Sort by numeric score
	 * @param  {Object} a Current value
	 * @param  {Object} b Next value
	 * @return {Number}   -1 for after previous,
	 *                    1 for before,
	 *                    0 for same location
	 */
	var sortByScore = exports.sortByScore = function sortByScore(a, b) {
	  return a.score - b.score;
	};

	/**
	 * Trigger native event
	 * @param  {NodeElement} element Element to trigger event on
	 * @param  {String} type         Type of event to trigger
	 * @param  {Object} customArgs   Data to pass with event
	 * @return {Object}              Triggered event
	 */
	var triggerEvent = exports.triggerEvent = function triggerEvent(element, type) {
	  var customArgs = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

	  var event = new CustomEvent(type, {
	    detail: customArgs,
	    bubbles: true,
	    cancelable: true
	  });

	  return element.dispatchEvent(event);
	};

/***/ }),
/* 33 */
/***/ (function(module, exports) {

	'use strict';

	/* eslint-disable */
	(function () {
	  // Production steps of ECMA-262, Edition 6, 22.1.2.1
	  // Reference: https://people.mozilla.org/~jorendorff/es6-draft.html#sec-array.from
	  if (!Array.from) {
	    Array.from = function () {
	      var toStr = Object.prototype.toString;

	      var isCallable = function isCallable(fn) {
	        return typeof fn === 'function' || toStr.call(fn) === '[object Function]';
	      };

	      var toInteger = function toInteger(value) {
	        var number = Number(value);
	        if (isNaN(number)) {
	          return 0;
	        }
	        if (number === 0 || !isFinite(number)) {
	          return number;
	        }
	        return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
	      };

	      var maxSafeInteger = Math.pow(2, 53) - 1;

	      var toLength = function toLength(value) {
	        var len = toInteger(value);
	        return Math.min(Math.max(len, 0), maxSafeInteger);
	      };

	      // The length property of the from method is 1.
	      return function from(arrayLike /*, mapFn, thisArg */) {
	        // 1. Let C be the this value.
	        var C = this;

	        // 2. Let items be ToObject(arrayLike).
	        var items = Object(arrayLike);

	        // 3. ReturnIfAbrupt(items).
	        if (arrayLike == null) {
	          throw new TypeError("Array.from requires an array-like object - not null or undefined");
	        }

	        // 4. If mapfn is undefined, then let mapping be false.
	        var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
	        var T;
	        if (typeof mapFn !== 'undefined') {
	          // 5. else
	          // 5. a If IsCallable(mapfn) is false, throw a TypeError exception.
	          if (!isCallable(mapFn)) {
	            throw new TypeError('Array.from: when provided, the second argument must be a function');
	          }

	          // 5. b. If thisArg was supplied, let T be thisArg; else let T be undefined.
	          if (arguments.length > 2) {
	            T = arguments[2];
	          }
	        }

	        // 10. Let lenValue be Get(items, "length").
	        // 11. Let len be ToLength(lenValue).
	        var len = toLength(items.length);

	        // 13. If IsConstructor(C) is true, then
	        // 13. a. Let A be the result of calling the [[Construct]] internal method of C with an argument list containing the single item len.
	        // 14. a. Else, Let A be ArrayCreate(len).
	        var A = isCallable(C) ? Object(new C(len)) : new Array(len);

	        // 16. Let k be 0.
	        var k = 0;
	        // 17. Repeat, while k < len… (also steps a - h)
	        var kValue;
	        while (k < len) {
	          kValue = items[k];
	          if (mapFn) {
	            A[k] = typeof T === 'undefined' ? mapFn(kValue, k) : mapFn.call(T, kValue, k);
	          } else {
	            A[k] = kValue;
	          }
	          k += 1;
	        }
	        // 18. Let putStatus be Put(A, "length", len, true).
	        A.length = len;
	        // 20. Return A.
	        return A;
	      };
	    }();
	  }

	  // Reference: https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Array/find
	  if (!Array.prototype.find) {
	    Array.prototype.find = function (predicate) {
	      'use strict';

	      if (this == null) {
	        throw new TypeError('Array.prototype.find called on null or undefined');
	      }
	      if (typeof predicate !== 'function') {
	        throw new TypeError('predicate must be a function');
	      }
	      var list = Object(this);
	      var length = list.length >>> 0;
	      var thisArg = arguments[1];
	      var value;

	      for (var i = 0; i < length; i++) {
	        value = list[i];
	        if (predicate.call(thisArg, value, i, list)) {
	          return value;
	        }
	      }
	      return undefined;
	    };
	  }

	  function CustomEvent(event, params) {
	    params = params || {
	      bubbles: false,
	      cancelable: false,
	      detail: undefined
	    };
	    var evt = document.createEvent('CustomEvent');
	    evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
	    return evt;
	  }

	  CustomEvent.prototype = window.Event.prototype;

	  window.CustomEvent = CustomEvent;
	})();

/***/ })
/******/ ])
});
;
//# sourceMappingURL=choices.js.map
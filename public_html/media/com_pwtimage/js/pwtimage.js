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

/*! Sortable 1.6.0 - MIT | git://github.com/rubaxa/Sortable.git */
!function(a){"use strict";"function"==typeof define&&define.amd?define(a):"undefined"!=typeof module&&"undefined"!=typeof module.exports?module.exports=a():window.Sortable=a()}(function(){"use strict";function a(a,b){if(!a||!a.nodeType||1!==a.nodeType)throw"Sortable: `el` must be HTMLElement, and not "+{}.toString.call(a);this.el=a,this.options=b=t({},b),a[T]=this;var c={group:Math.random(),sort:!0,disabled:!1,store:null,handle:null,scroll:!0,scrollSensitivity:30,scrollSpeed:10,draggable:/[uo]l/i.test(a.nodeName)?"li":">*",ghostClass:"sortable-ghost",chosenClass:"sortable-chosen",dragClass:"sortable-drag",ignore:"a, img",filter:null,preventOnFilter:!0,animation:0,setData:function(a,b){a.setData("Text",b.textContent)},dropBubble:!1,dragoverBubble:!1,dataIdAttr:"data-id",delay:0,forceFallback:!1,fallbackClass:"sortable-fallback",fallbackOnBody:!1,fallbackTolerance:0,fallbackOffset:{x:0,y:0}};for(var d in c)!(d in b)&&(b[d]=c[d]);ga(b);for(var e in this)"_"===e.charAt(0)&&"function"==typeof this[e]&&(this[e]=this[e].bind(this));this.nativeDraggable=!b.forceFallback&&$,f(a,"mousedown",this._onTapStart),f(a,"touchstart",this._onTapStart),f(a,"pointerdown",this._onTapStart),this.nativeDraggable&&(f(a,"dragover",this),f(a,"dragenter",this)),ea.push(this._onDragOver),b.store&&this.sort(b.store.get(this))}function b(a,b){"clone"!==a.lastPullMode&&(b=!0),z&&z.state!==b&&(i(z,"display",b?"none":""),b||z.state&&(a.options.group.revertClone?(A.insertBefore(z,B),a._animate(w,z)):A.insertBefore(z,w)),z.state=b)}function c(a,b,c){if(a){c=c||V;do if(">*"===b&&a.parentNode===c||r(a,b))return a;while(a=d(a))}return null}function d(a){var b=a.host;return b&&b.nodeType?b:a.parentNode}function e(a){a.dataTransfer&&(a.dataTransfer.dropEffect="move"),a.preventDefault()}function f(a,b,c){a.addEventListener(b,c,Z)}function g(a,b,c){a.removeEventListener(b,c,Z)}function h(a,b,c){if(a)if(a.classList)a.classList[c?"add":"remove"](b);else{var d=(" "+a.className+" ").replace(R," ").replace(" "+b+" "," ");a.className=(d+(c?" "+b:"")).replace(R," ")}}function i(a,b,c){var d=a&&a.style;if(d){if(void 0===c)return V.defaultView&&V.defaultView.getComputedStyle?c=V.defaultView.getComputedStyle(a,""):a.currentStyle&&(c=a.currentStyle),void 0===b?c:c[b];b in d||(b="-webkit-"+b),d[b]=c+("string"==typeof c?"":"px")}}function j(a,b,c){if(a){var d=a.getElementsByTagName(b),e=0,f=d.length;if(c)for(;e<f;e++)c(d[e],e);return d}return[]}function k(a,b,c,d,e,f,g){a=a||b[T];var h=V.createEvent("Event"),i=a.options,j="on"+c.charAt(0).toUpperCase()+c.substr(1);h.initEvent(c,!0,!0),h.to=b,h.from=e||b,h.item=d||b,h.clone=z,h.oldIndex=f,h.newIndex=g,b.dispatchEvent(h),i[j]&&i[j].call(a,h)}function l(a,b,c,d,e,f,g,h){var i,j,k=a[T],l=k.options.onMove;return i=V.createEvent("Event"),i.initEvent("move",!0,!0),i.to=b,i.from=a,i.dragged=c,i.draggedRect=d,i.related=e||b,i.relatedRect=f||b.getBoundingClientRect(),i.willInsertAfter=h,a.dispatchEvent(i),l&&(j=l.call(k,i,g)),j}function m(a){a.draggable=!1}function n(){aa=!1}function o(a,b){var c=a.lastElementChild,d=c.getBoundingClientRect();return b.clientY-(d.top+d.height)>5||b.clientX-(d.left+d.width)>5}function p(a){for(var b=a.tagName+a.className+a.src+a.href+a.textContent,c=b.length,d=0;c--;)d+=b.charCodeAt(c);return d.toString(36)}function q(a,b){var c=0;if(!a||!a.parentNode)return-1;for(;a&&(a=a.previousElementSibling);)"TEMPLATE"===a.nodeName.toUpperCase()||">*"!==b&&!r(a,b)||c++;return c}function r(a,b){if(a){b=b.split(".");var c=b.shift().toUpperCase(),d=new RegExp("\\s("+b.join("|")+")(?=\\s)","g");return!(""!==c&&a.nodeName.toUpperCase()!=c||b.length&&((" "+a.className+" ").match(d)||[]).length!=b.length)}return!1}function s(a,b){var c,d;return function(){void 0===c&&(c=arguments,d=this,setTimeout(function(){1===c.length?a.call(d,c[0]):a.apply(d,c),c=void 0},b))}}function t(a,b){if(a&&b)for(var c in b)b.hasOwnProperty(c)&&(a[c]=b[c]);return a}function u(a){return X?X(a).clone(!0)[0]:Y&&Y.dom?Y.dom(a).cloneNode(!0):a.cloneNode(!0)}function v(a){for(var b=a.getElementsByTagName("input"),c=b.length;c--;){var d=b[c];d.checked&&da.push(d)}}if("undefined"==typeof window||!window.document)return function(){throw new Error("Sortable.js requires a window with a document")};var w,x,y,z,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q={},R=/\s+/g,S=/left|right|inline/,T="Sortable"+(new Date).getTime(),U=window,V=U.document,W=U.parseInt,X=U.jQuery||U.Zepto,Y=U.Polymer,Z=!1,$=!!("draggable"in V.createElement("div")),_=function(a){return!navigator.userAgent.match(/Trident.*rv[ :]?11\./)&&(a=V.createElement("x"),a.style.cssText="pointer-events:auto","auto"===a.style.pointerEvents)}(),aa=!1,ba=Math.abs,ca=Math.min,da=[],ea=[],fa=s(function(a,b,c){if(c&&b.scroll){var d,e,f,g,h,i,j=c[T],k=b.scrollSensitivity,l=b.scrollSpeed,m=a.clientX,n=a.clientY,o=window.innerWidth,p=window.innerHeight;if(E!==c&&(D=b.scroll,E=c,F=b.scrollFn,D===!0)){D=c;do if(D.offsetWidth<D.scrollWidth||D.offsetHeight<D.scrollHeight)break;while(D=D.parentNode)}D&&(d=D,e=D.getBoundingClientRect(),f=(ba(e.right-m)<=k)-(ba(e.left-m)<=k),g=(ba(e.bottom-n)<=k)-(ba(e.top-n)<=k)),f||g||(f=(o-m<=k)-(m<=k),g=(p-n<=k)-(n<=k),(f||g)&&(d=U)),Q.vx===f&&Q.vy===g&&Q.el===d||(Q.el=d,Q.vx=f,Q.vy=g,clearInterval(Q.pid),d&&(Q.pid=setInterval(function(){return i=g?g*l:0,h=f?f*l:0,"function"==typeof F?F.call(j,h,i,a):void(d===U?U.scrollTo(U.pageXOffset+h,U.pageYOffset+i):(d.scrollTop+=i,d.scrollLeft+=h))},24)))}},30),ga=function(a){function b(a,b){return void 0!==a&&a!==!0||(a=c.name),"function"==typeof a?a:function(c,d){var e=d.options.group.name;return b?a:a&&(a.join?a.indexOf(e)>-1:e==a)}}var c={},d=a.group;d&&"object"==typeof d||(d={name:d}),c.name=d.name,c.checkPull=b(d.pull,!0),c.checkPut=b(d.put),c.revertClone=d.revertClone,a.group=c};a.prototype={constructor:a,_onTapStart:function(a){var b,d=this,e=this.el,f=this.options,g=f.preventOnFilter,h=a.type,i=a.touches&&a.touches[0],j=(i||a).target,l=a.target.shadowRoot&&a.path[0]||j,m=f.filter;if(v(e),!w&&!("mousedown"===h&&0!==a.button||f.disabled)&&(j=c(j,f.draggable,e),j&&C!==j)){if(b=q(j,f.draggable),"function"==typeof m){if(m.call(this,a,j,this))return k(d,l,"filter",j,e,b),void(g&&a.preventDefault())}else if(m&&(m=m.split(",").some(function(a){if(a=c(l,a.trim(),e))return k(d,a,"filter",j,e,b),!0})))return void(g&&a.preventDefault());f.handle&&!c(l,f.handle,e)||this._prepareDragStart(a,i,j,b)}},_prepareDragStart:function(a,b,c,d){var e,g=this,i=g.el,l=g.options,n=i.ownerDocument;c&&!w&&c.parentNode===i&&(N=a,A=i,w=c,x=w.parentNode,B=w.nextSibling,C=c,L=l.group,J=d,this._lastX=(b||a).clientX,this._lastY=(b||a).clientY,w.style["will-change"]="transform",e=function(){g._disableDelayedDrag(),w.draggable=g.nativeDraggable,h(w,l.chosenClass,!0),g._triggerDragStart(a,b),k(g,A,"choose",w,A,J)},l.ignore.split(",").forEach(function(a){j(w,a.trim(),m)}),f(n,"mouseup",g._onDrop),f(n,"touchend",g._onDrop),f(n,"touchcancel",g._onDrop),f(n,"pointercancel",g._onDrop),f(n,"selectstart",g),l.delay?(f(n,"mouseup",g._disableDelayedDrag),f(n,"touchend",g._disableDelayedDrag),f(n,"touchcancel",g._disableDelayedDrag),f(n,"mousemove",g._disableDelayedDrag),f(n,"touchmove",g._disableDelayedDrag),f(n,"pointermove",g._disableDelayedDrag),g._dragStartTimer=setTimeout(e,l.delay)):e())},_disableDelayedDrag:function(){var a=this.el.ownerDocument;clearTimeout(this._dragStartTimer),g(a,"mouseup",this._disableDelayedDrag),g(a,"touchend",this._disableDelayedDrag),g(a,"touchcancel",this._disableDelayedDrag),g(a,"mousemove",this._disableDelayedDrag),g(a,"touchmove",this._disableDelayedDrag),g(a,"pointermove",this._disableDelayedDrag)},_triggerDragStart:function(a,b){b=b||("touch"==a.pointerType?a:null),b?(N={target:w,clientX:b.clientX,clientY:b.clientY},this._onDragStart(N,"touch")):this.nativeDraggable?(f(w,"dragend",this),f(A,"dragstart",this._onDragStart)):this._onDragStart(N,!0);try{V.selection?setTimeout(function(){V.selection.empty()}):window.getSelection().removeAllRanges()}catch(a){}},_dragStarted:function(){if(A&&w){var b=this.options;h(w,b.ghostClass,!0),h(w,b.dragClass,!1),a.active=this,k(this,A,"start",w,A,J)}else this._nulling()},_emulateDragOver:function(){if(O){if(this._lastX===O.clientX&&this._lastY===O.clientY)return;this._lastX=O.clientX,this._lastY=O.clientY,_||i(y,"display","none");var a=V.elementFromPoint(O.clientX,O.clientY),b=a,c=ea.length;if(b)do{if(b[T]){for(;c--;)ea[c]({clientX:O.clientX,clientY:O.clientY,target:a,rootEl:b});break}a=b}while(b=b.parentNode);_||i(y,"display","")}},_onTouchMove:function(b){if(N){var c=this.options,d=c.fallbackTolerance,e=c.fallbackOffset,f=b.touches?b.touches[0]:b,g=f.clientX-N.clientX+e.x,h=f.clientY-N.clientY+e.y,j=b.touches?"translate3d("+g+"px,"+h+"px,0)":"translate("+g+"px,"+h+"px)";if(!a.active){if(d&&ca(ba(f.clientX-this._lastX),ba(f.clientY-this._lastY))<d)return;this._dragStarted()}this._appendGhost(),P=!0,O=f,i(y,"webkitTransform",j),i(y,"mozTransform",j),i(y,"msTransform",j),i(y,"transform",j),b.preventDefault()}},_appendGhost:function(){if(!y){var a,b=w.getBoundingClientRect(),c=i(w),d=this.options;y=w.cloneNode(!0),h(y,d.ghostClass,!1),h(y,d.fallbackClass,!0),h(y,d.dragClass,!0),i(y,"top",b.top-W(c.marginTop,10)),i(y,"left",b.left-W(c.marginLeft,10)),i(y,"width",b.width),i(y,"height",b.height),i(y,"opacity","0.8"),i(y,"position","fixed"),i(y,"zIndex","100000"),i(y,"pointerEvents","none"),d.fallbackOnBody&&V.body.appendChild(y)||A.appendChild(y),a=y.getBoundingClientRect(),i(y,"width",2*b.width-a.width),i(y,"height",2*b.height-a.height)}},_onDragStart:function(a,b){var c=a.dataTransfer,d=this.options;this._offUpEvents(),L.checkPull(this,this,w,a)&&(z=u(w),z.draggable=!1,z.style["will-change"]="",i(z,"display","none"),h(z,this.options.chosenClass,!1),A.insertBefore(z,w),k(this,A,"clone",w)),h(w,d.dragClass,!0),b?("touch"===b?(f(V,"touchmove",this._onTouchMove),f(V,"touchend",this._onDrop),f(V,"touchcancel",this._onDrop),f(V,"pointermove",this._onTouchMove),f(V,"pointerup",this._onDrop)):(f(V,"mousemove",this._onTouchMove),f(V,"mouseup",this._onDrop)),this._loopId=setInterval(this._emulateDragOver,50)):(c&&(c.effectAllowed="move",d.setData&&d.setData.call(this,c,w)),f(V,"drop",this),setTimeout(this._dragStarted,0))},_onDragOver:function(d){var e,f,g,h,j=this.el,k=this.options,m=k.group,p=a.active,q=L===m,r=!1,s=k.sort;if(void 0!==d.preventDefault&&(d.preventDefault(),!k.dragoverBubble&&d.stopPropagation()),!w.animated&&(P=!0,p&&!k.disabled&&(q?s||(h=!A.contains(w)):M===this||(p.lastPullMode=L.checkPull(this,p,w,d))&&m.checkPut(this,p,w,d))&&(void 0===d.rootEl||d.rootEl===this.el))){if(fa(d,k,this.el),aa)return;if(e=c(d.target,k.draggable,j),f=w.getBoundingClientRect(),M!==this&&(M=this,r=!0),h)return b(p,!0),x=A,void(z||B?A.insertBefore(w,z||B):s||A.appendChild(w));if(0===j.children.length||j.children[0]===y||j===d.target&&o(j,d)){if(0!==j.children.length&&j.children[0]!==y&&j===d.target&&(e=j.lastElementChild),e){if(e.animated)return;g=e.getBoundingClientRect()}b(p,q),l(A,j,w,f,e,g,d)!==!1&&(w.contains(j)||(j.appendChild(w),x=j),this._animate(f,w),e&&this._animate(g,e))}else if(e&&!e.animated&&e!==w&&void 0!==e.parentNode[T]){G!==e&&(G=e,H=i(e),I=i(e.parentNode)),g=e.getBoundingClientRect();var t=g.right-g.left,u=g.bottom-g.top,v=S.test(H.cssFloat+H.display)||"flex"==I.display&&0===I["flex-direction"].indexOf("row"),C=e.offsetWidth>w.offsetWidth,D=e.offsetHeight>w.offsetHeight,E=(v?(d.clientX-g.left)/t:(d.clientY-g.top)/u)>.5,F=e.nextElementSibling,J=!1;if(v){var K=w.offsetTop,N=e.offsetTop;J=K===N?e.previousElementSibling===w&&!C||E&&C:e.previousElementSibling===w||w.previousElementSibling===e?(d.clientY-g.top)/u>.5:N>K}else r||(J=F!==w&&!D||E&&D);var O=l(A,j,w,f,e,g,d,J);O!==!1&&(1!==O&&O!==-1||(J=1===O),aa=!0,setTimeout(n,30),b(p,q),w.contains(j)||(J&&!F?j.appendChild(w):e.parentNode.insertBefore(w,J?F:e)),x=w.parentNode,this._animate(f,w),this._animate(g,e))}}},_animate:function(a,b){var c=this.options.animation;if(c){var d=b.getBoundingClientRect();1===a.nodeType&&(a=a.getBoundingClientRect()),i(b,"transition","none"),i(b,"transform","translate3d("+(a.left-d.left)+"px,"+(a.top-d.top)+"px,0)"),b.offsetWidth,i(b,"transition","all "+c+"ms"),i(b,"transform","translate3d(0,0,0)"),clearTimeout(b.animated),b.animated=setTimeout(function(){i(b,"transition",""),i(b,"transform",""),b.animated=!1},c)}},_offUpEvents:function(){var a=this.el.ownerDocument;g(V,"touchmove",this._onTouchMove),g(V,"pointermove",this._onTouchMove),g(a,"mouseup",this._onDrop),g(a,"touchend",this._onDrop),g(a,"pointerup",this._onDrop),g(a,"touchcancel",this._onDrop),g(a,"pointercancel",this._onDrop),g(a,"selectstart",this)},_onDrop:function(b){var c=this.el,d=this.options;clearInterval(this._loopId),clearInterval(Q.pid),clearTimeout(this._dragStartTimer),g(V,"mousemove",this._onTouchMove),this.nativeDraggable&&(g(V,"drop",this),g(c,"dragstart",this._onDragStart)),this._offUpEvents(),b&&(P&&(b.preventDefault(),!d.dropBubble&&b.stopPropagation()),y&&y.parentNode&&y.parentNode.removeChild(y),A!==x&&"clone"===a.active.lastPullMode||z&&z.parentNode&&z.parentNode.removeChild(z),w&&(this.nativeDraggable&&g(w,"dragend",this),m(w),w.style["will-change"]="",h(w,this.options.ghostClass,!1),h(w,this.options.chosenClass,!1),k(this,A,"unchoose",w,A,J),A!==x?(K=q(w,d.draggable),K>=0&&(k(null,x,"add",w,A,J,K),k(this,A,"remove",w,A,J,K),k(null,x,"sort",w,A,J,K),k(this,A,"sort",w,A,J,K))):w.nextSibling!==B&&(K=q(w,d.draggable),K>=0&&(k(this,A,"update",w,A,J,K),k(this,A,"sort",w,A,J,K))),a.active&&(null!=K&&K!==-1||(K=J),k(this,A,"end",w,A,J,K),this.save()))),this._nulling()},_nulling:function(){A=w=x=y=B=z=C=D=E=N=O=P=K=G=H=M=L=a.active=null,da.forEach(function(a){a.checked=!0}),da.length=0},handleEvent:function(a){switch(a.type){case"drop":case"dragend":this._onDrop(a);break;case"dragover":case"dragenter":w&&(this._onDragOver(a),e(a));break;case"selectstart":a.preventDefault()}},toArray:function(){for(var a,b=[],d=this.el.children,e=0,f=d.length,g=this.options;e<f;e++)a=d[e],c(a,g.draggable,this.el)&&b.push(a.getAttribute(g.dataIdAttr)||p(a));return b},sort:function(a){var b={},d=this.el;this.toArray().forEach(function(a,e){var f=d.children[e];c(f,this.options.draggable,d)&&(b[a]=f)},this),a.forEach(function(a){b[a]&&(d.removeChild(b[a]),d.appendChild(b[a]))})},save:function(){var a=this.options.store;a&&a.set(this)},closest:function(a,b){return c(a,b||this.options.draggable,this.el)},option:function(a,b){var c=this.options;return void 0===b?c[a]:(c[a]=b,void("group"===a&&ga(c)))},destroy:function(){var a=this.el;a[T]=null,g(a,"mousedown",this._onTapStart),g(a,"touchstart",this._onTapStart),g(a,"pointerdown",this._onTapStart),this.nativeDraggable&&(g(a,"dragover",this),g(a,"dragenter",this)),Array.prototype.forEach.call(a.querySelectorAll("[draggable]"),function(a){a.removeAttribute("draggable")}),ea.splice(ea.indexOf(this._onDragOver),1),this._onDrop(),this.el=a=null}},f(V,"touchmove",function(b){a.active&&b.preventDefault()});try{window.addEventListener("test",null,Object.defineProperty({},"passive",{get:function(){Z={capture:!1,passive:!1}}}))}catch(a){}return a.utils={on:f,off:g,css:i,find:j,is:function(a,b){return!!c(a,b,a)},extend:t,throttle:s,closest:c,toggleClass:h,clone:u,index:q},a.create=function(b,c){return new a(b,c)},a.version="1.6.0",a});
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
        imageLimit = 100;

    /**
     * Creates another image selector instance.
     */
    pwtImage.addRepeatImage = function () {
        // Duplicate the controls
        var imageField = jQuery('div.js-image-controls');

        // Duplicate the first image block
        var imageBlock = imageField.first().clone();

        // Get a new unique ID
        var modalId = new Date().getTime();

        // Get the current ID value
        var currentId = imageBlock.children(':first').prop('id').split('_')[0];

        // Replace all IDs with a new ID
        imageBlock.prop('id', modalId);

        // Set the required IDs
        imageBlock.children('#' + currentId + '_preview').prop('id', modalId + '_preview').html('');
        imageBlock.children('#' + currentId + '_clear').prop('id', modalId + '_clear').addClass('hidden').prop('onclick', 'pwtImage.clearImage(\'' + modalId + '\');');
        imageBlock.children('button#label_modal_1').prop('onclick', 'pwtImage.setTargetId(\'' + modalId + '\');');

        // Add the new image to the DOM
        imageField.last().after(imageBlock);
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
     * Set if we are called from a WYSIWYG editor
     */
    pwtImage.setWysiwyg = function (value) {
        wysiwyg = value;
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
     * @param tokenName
     * @param tokenValue
     * @param createNew
     */
    pwtImage.saveImage = function (id, tokenName, tokenValue, createNew) {
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
            setwidth = width,
            ratio = jQuery('#' + id + ' .js-pwt-image-ratio').val(),
            sourcePath = jQuery('#' + id + ' .js-pwt-image-sourcePath').val(),
            subPath = jQuery('#' + id + ' .js-pwt-image-subPath').val(),
            localfile = jQuery('#' + id + ' .js-pwt-image-localfile').val(),
            targetfile = jQuery('#' + id + ' .js-pwt-image-targetfile').val(),
            widthOptions = jQuery('#' + id + '_widthOptions').val(),
            storeFolder = jQuery('#' + id + '_storeFolder').val(),
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
                storeFolder = jQuery('#' + id + '_selectedFolder').val();
                break;
            case 'default':
                storeFolder = subPath;
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
        data.append('image', image);
        data.append('pwt-image-localFile', localfile);
        data.append('pwt-image-targetFile', targetfile);
        data.append('set-width', setwidth);
        data.append('widthOptions', widthOptions);
        data.append('storeFolder', storeFolder);
        data.append('alt', altText);
        data.append('caption', captionText);
        data.append('pwt-image-data', crop);
        data.append('pwt-image-width', width);
        data.append('pwt-image-ratio', ratio);
        data.append('pwt-image-sourcePath', sourcePath);
        data.append('pwt-image-subPath', subPath);
        data.append(tokenName, tokenValue);

        // Find the target ID
        var targetId = pwtImage.getTargetId();

        // Try to upload and process the image
        jQuery.ajax({
            type: 'POST',
            data: data,
            contentType: false,
            url: postUrl,
            cache: false,
            processData: false,
            async: false,
            success: function (response) {
                // Render the Joomla message
                if (response.message) {
                    window.parent.Joomla.renderMessages({warning: [response.message]});
                }

                if (response.messages) {
                    window.parent.Joomla.renderMessages(response.messages);
                }

                // Set the created result file
                resultFile = response.data;

                if (!wysiwyg) {
                    window.parent.jQuery('#' + targetId + '_value').val(resultFile);
                    window.parent.jQuery('#' + targetId + '_preview').html('<img src="../' + resultFile + '" />');
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

                   return false;
                }
            },
            error: function (response) {
                jQuery('#' + targetId + '_preview img').prop('src', '');
                Joomla.renderMessages({error: [Joomla.JText._('COM_PWTIMAGE_SAVE_FAILED', 'There was a problem to save the file')]});
                console.log('Image upload failed: ' + response.responseText);
            }
        });

        // Switch back to upload tab if user wants to create another image
        if (createNew)
        {
            // Clear variables
            altText = '';
            captionText = '';
            resultFile = '';
            jQuery('#' + id + ' .js-pwt-image-localfile').val('');
            jQuery('#' + id + '_upload').replaceWith(jQuery('#' + id + '_upload').val('').clone(true));

            // Clean up the crop data
            jQuery('#' + id + ' .js-pwt-image-data').val('');

            // Reload the images
            if (storeFolder.length === 0) {
                sourcePath = jQuery('#' + id + ' .js-sourcePath').text();
                storeFolder = sourcePath.substring(0, (sourcePath.length - 1));
            }

            pwtImage.loadFolder('#' + id, storeFolder, 'select', tokenName, tokenValue);

            // Reset the Edit page
            var fulltab = jQuery('.pwt-fulltab-message');
            fulltab.removeClass('is-hidden');
            fulltab.next().addClass('is-hidden');

            jQuery('[href="#select"]').trigger('click');
        }

        pwtImage.cancelImage(id);

        return false;
    };

    /**
     * Save the gallery images as a comma separated list
     */
    pwtImage.saveGallery = function() {
        var images = [];

        // Get all the gallery images
        jQuery('#gallery #js-sortable-result img').each(function(index, item) {
            // Construct the image name
            var imageName = jQuery(item).prop('src').replace(/\//g, '_');

            // Get the alt value
            var imageAlt = jQuery('#gallery-image-info input[name="input_' + imageName + '_alt"]').val();

            // Get the caption value
            var imageCaption = jQuery('#gallery-image-info input[name="input_' + imageName + '_caption"]').val();

            images.push(jQuery(item).prop('src') + ',' + imageAlt + ',' + imageCaption);
        });

        // Join all images into a single string
        resultFile = images.join('|');

        // Inform the form that a gallery is being added
        jQuery('form.js-image-form #galleryPath').val(1);

        pwtImage.closeModal();
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
     * @param tokenName
     * @param tokenValue
     * @returns {boolean}
     */
    pwtImage.loadFolder = function (element, folder, target, tokenName, tokenValue) {

        var id = getParentId(element);
        var data = new FormData();
        var postUrl = jQuery('#post-url').val();

        // Add the form data
        data.append('option', 'com_pwtimage');
        data.append('task', 'image.loadFolder');
        data.append('format', 'json');
        data.append('folder', folder);
        data.append(tokenName, tokenValue);

        // Load the subfolders of given folder
        jQuery.ajax({
            type: 'POST',
            data: data,
            url: postUrl,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                if (response.message) {
                    console.log('Failed to load subfolders from folder ' + folder + '. Message: ' + response.message);
                    jQuery('.pwt-gallery__items--images').html('<div class="alert alert-error">' + response.message + '</div>');
                } else if (response.messages) {
                    for (index = 0; index < response.messages.warning.length; ++index) {
                        jQuery('.pwt-gallery__items--images').append('<div class="alert alert-error">' + response.messages.warning[index] + '</div>');
                    }
                } else {
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

                                // Make sure we start with a forward slash
                                if (structure.substring(0, 1) !== '/') {
                                    structure = '/' + structure;
                                }

                                folderPath[index + 1] = '<a href="' + structure + '" onclick="pwtImage.loadFolder(\'.pwt-gallery__items--folders\', \'' + structure + '\', \'' + target + '\', \'' + tokenName + '\', \'' + tokenValue + '\'); return false;"><span class="icon-folder-2"></span>' + folderItem + '</a>';
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

                            link.push('<div class="pwt-gallery__item"><a href="' + itemPath + '" onclick="pwtImage.loadFolder(\'.pwt-gallery__items--folders\', \'' + itemPath + '\', \'' + target + '\', \'' + tokenName + '\', \'' + tokenValue + '\'); return false;">' +
                                '<div class="pwt-gallery__item__content">' +
                                '<span class="pwt-gallery__item__icon icon-folder-2"></span>' +
                                '<span class="pwt-gallery__item__title">' + item + '</span>' +
                                '</div>' +
                                '</a></div>');
                        });

                        // Add the folders
                        if (link.length) {
                            jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--folders').html(link.join(' '));
                        } else {
                            jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--folders').html('');
                        }
                    }

                    // Add the files
                    link = [];
                    var pagination = [];

                    if (response.data.files) {
                        // Setup pagination
                        var pages = Math.ceil(response.data.files.length / imageLimit);

                        for (var page = 1; page <= pages; page++) {
                            pagination.push('<a href="#" onclick="pwtImage.showMoreImages(\'.pwt-gallery__items--images\', ' + page + ', \'' + target + '\');">' + page + '</a>');
                        }

                        if (response.data.files.length > imageLimit) {
                            addToLocalStorage(id, 'files', response.data.files);
                            addToLocalStorage(id, 'basePath', response.data.basePath);
                        }

                        var files = response.data.files.slice(0, imageLimit);

                        jQuery(files).each(function (index, item) {
                            var separator = '/';

                            if (response.data.basePath.substring(response.data.basePath.length - 1) === '/') {
                                separator = '';
                            }

                            var itemPath = response.data.basePath + separator + item;

                            link.push(getImageElement(item, itemPath, target));

                        });

                        // Add the files
                        if (link.length) {
                            jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--images').html(link.join(' '));
                        } else {
                            jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--images').html('');
                        }

                        // Add the pagination
                        if (link.length) {
                            jQuery('#' + id + ' #' + target + ' .pwt-pagination').html('<div class="pwt-pagination__pages">' + pagination.join(' ') + '</div>');
                        } else {
                            jQuery('#' + id + ' #' + target + ' .pwt-pagination').html('');
                        }

                        jQuery('.pwt-gallery__item img').each(function () {
                            jQuery(this).load(function () {
                                if (this.width > this.height) {
                                    jQuery(this).addClass('is-landscape');
                                }
                            });
                        });
                    }
                }
            },
            error: function (response) {
                console.log('Failed to load folder: ' + response.responseText);
                console.log('Response code: ' + response.status + ' ' + response.statusText);
            }
        });

        if (target === 'gallery') {
            setupGallery();
        }

        return false;
    };

    /**
     * Pagination class
     *
     * @param element
     * @param page
     */
    pwtImage.showMoreImages = function (element, page, target) {
        var id = getParentId(element),
            storedFiles = getFromLocalStorage(id, 'files'),
            basePath = getFromLocalStorage(id, 'basePath'),
            start = ((page === 1) ? 0 : (page - 1) * imageLimit),
            end = page * imageLimit,
            files = storedFiles.slice(start, end),
            link = [];

        jQuery(files).each(function (index, item) {
            var itemPath = basePath + '/' + item;
            link.push(getImageElement(item, itemPath, target));
        });

        // Add the files
        if (link.length) {
            jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--images').html(link.join(' '));
        } else {
            jQuery('#' + id + ' #' + target + ' .pwt-gallery__items--images').html('');
        }
    };

    /**
     * Construct an image element
     */
    function getImageElement(item, itemPath, target) {
        var imageElement = '';

        switch (target) {
            case 'select':
                imageElement = '<div class="pwt-gallery__item">' +
                    '<a href="#" onclick="return pwtImage.previewImage(\'.pwt-gallery__items--images\', \'' + itemPath + '\');">' +
                    '<div class="pwt-gallery__item__image">' +
                    '<div class="pwt-gallery__item__center">' +
                    '<img src="' + itemPath + '" alt="' + baseName(item) + '" />' +
                    '</div>' +
                    '</div>' +
                    '</a>' +
                    '</div>';
                break;
            case 'gallery':
                imageElement = '<div class="pwt-gallery__item draggable-item">' +
                    '<a href="#" onclick="pwtImage.imageInfo(this);">' +
                    '<div class="pwt-gallery__item__image">' +
                    '<div class="pwt-gallery__item__center">' +
                    '<img src="' + itemPath + '" alt="' + baseName(item) + '" />' +
                    '</div>' +
                    '</div>' +
                    '</a>' +
                    '<button type="button" class="pwt-add-button js-add"><span class="visually-hidden">' + Joomla.JText._('COM_PWTIMAGE_GALLERY_BUTTON_ADD', 'Add') + '</span><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Plus</title><g transform="matrix(1.09091,0,0,1.09091,8.88178e-16,-2.18182)"><path d="M22,11.5L22,14.5C22,14.917 21.854,15.271 21.562,15.562C21.27,15.853 20.916,15.999 20.5,16L14,16L14,22.5C14,22.917 13.854,23.271 13.562,23.562C13.27,23.853 12.916,23.999 12.5,24L9.5,24C9.083,24 8.729,23.854 8.438,23.562C8.147,23.27 8.001,22.916 8,22.5L8,16L1.5,16C1.083,16 0.729,15.854 0.438,15.562C0.147,15.27 0.001,14.916 0,14.5L0,11.5C0,11.083 0.146,10.729 0.438,10.438C0.73,10.147 1.084,10.001 1.5,10L8,10L8,3.5C8,3.083 8.146,2.729 8.438,2.438C8.73,2.147 9.084,2.001 9.5,2L12.5,2C12.917,2 13.271,2.146 13.562,2.438C13.853,2.73 13.999,3.084 14,3.5L14,10L20.5,10C20.917,10 21.271,10.146 21.562,10.438C21.853,10.73 21.999,11.084 22,11.5Z"/></g></svg></button>' +
                    '<button type="button" class="pwt-info-button js-info" onclick="pwtImage.imageInfo(this);"><span class="visually-hidden">' + Joomla.JText._('COM_PWTIMAGE_GALLERY_BUTTON_INFO', 'Info') + '</span><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g transform="matrix(1.09091,0,0,1.09091,6.54545,-2.18182)"><path d="M10,21L10,23C10,23.271 9.901,23.505 9.703,23.703C9.505,23.901 9.271,24 9,24L1,24C0.729,24 0.495,23.901 0.297,23.703C0.099,23.505 0,23.271 0,23L0,21C0,20.729 0.099,20.495 0.297,20.297C0.495,20.099 0.729,20 1,20L2,20L2,14L1,14C0.729,14 0.495,13.901 0.297,13.703C0.099,13.505 0,13.271 0,13L0,11C0,10.729 0.099,10.495 0.297,10.297C0.495,10.099 0.729,10 1,10L7,10C7.271,10 7.505,10.099 7.703,10.297C7.901,10.495 8,10.729 8,11L8,20L9,20C9.271,20 9.505,20.099 9.703,20.297C9.901,20.495 10,20.729 10,21ZM8,3L8,6C8,6.271 7.901,6.505 7.703,6.703C7.505,6.901 7.271,7 7,7L3,7C2.729,7 2.495,6.901 2.297,6.703C2.099,6.505 2,6.271 2,6L2,3C2,2.729 2.099,2.495 2.297,2.297C2.495,2.099 2.729,2 3,2L7,2C7.271,2 7.505,2.099 7.703,2.297C7.901,2.495 8,2.729 8,3Z"/></g></svg></button>' +
                    '<button type="button" class="pwt-remove-button js-remove"><span class="visually-hidden">' + Joomla.JText._('COM_PWTIMAGE_GALLERY_BUTTON_REMOVE', 'Remove') + '</span><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>Minus</title><g transform="matrix(1.09091,0,0,1.09091,8.88178e-16,-2.18182)"><path d="M22,11.5L22,14.5C22,14.917 21.854,15.271 21.562,15.562C21.27,15.853 20.916,15.999 20.5,16L1.5,16C1.083,16 0.729,15.854 0.438,15.562C0.147,15.27 0.001,14.916 0,14.5L0,11.5C0,11.083 0.146,10.729 0.438,10.438C0.73,10.147 1.084,10.001 1.5,10L20.5,10C20.917,10 21.271,10.146 21.562,10.438C21.853,10.73 21.999,11.084 22,11.5Z"/></g></svg></button>' +
                    '</div>';
                break;
        }

        return imageElement;
    }

    /**
     * This adds a file on the server to the canvas for cropping
     *
     * @param element  The page element to find the ID for.
     * @param file     The selected image on the server
     * @param image
     * @param upload
     * @param viewMode
     *
     * @returns {boolean}
     */
    pwtImage.addImageToCanvas = function (element, file, image, upload, viewMode) {
        var id = getParentId(element);

        if (upload === undefined || upload === null) {
            upload = false;
        }

        if (viewMode === undefined || viewMode === null) {
            viewMode = 1;
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

            // Set the image value in the form
            var fileName = file.split('/').slice(-1)[0];
            jQuery('#' + id + ' .js-pwt-image-targetfile').val(fileName);
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

        switch (choicesDestination.getValue().value) {
            case 'default':
                jQuery('#' + id + '_enterFolder').addClass('is-visible').removeClass('is-hidden');
                jQuery('#' + id + '_storeFolder').prop('disabled', true).prop('placeholder', jQuery('.js-pwt-image-subPath').val());
                jQuery('#' + id + '_selectFolder').addClass('is-hidden').removeClass('is-visible');
                break;
            case 'select':
                jQuery('#' + id + '_enterFolder').addClass('is-hidden').removeClass('is-visible');
                jQuery('#' + id + '_selectFolder').addClass('is-visible').removeClass('is-hidden');
                break;
            case 'custom':
                jQuery('#' + id + '_enterFolder').addClass('is-visible').removeClass('is-hidden');
                jQuery('#' + id + '_storeFolder').prop('disabled', false).prop('placeholder', '');
                jQuery('#' + id + '_selectFolder').addClass('is-hidden').removeClass('is-visible');
                break;
        }

        jQuery('#' + id + '_selectFolder').trigger('change');
    };

    /**
     * Sortable list for gallery
     *
     * Initialise the gallery
     */
    function setupGallery() {
        // Grab containers
        var sortOrigin = document.getElementById('js-sortable-origin');
        var sortResult = document.getElementById('js-sortable-result');

        // Create sortable variables
        var sortableOrigin = Sortable.create(sortOrigin, {
            group: {
                name: 'origin',
                pull: 'clone'
            },
            sort: false,
            animation: 150,
            onStart: function (evt) {
                evt.clone.classList.add('is-undraggable');
            },
            onEnd: function (evt) {
                galleryCount();
            }
        });

        var sortableResult = Sortable.create(sortResult, {
            group: {
                name: 'results',
                put: 'origin'
            },
            animation: 150,
            filter: '.js-remove',
            onFilter: function (evt) {
                var el = sortableResult.closest(evt.item); // get dragged item
                var src = el.querySelector('img').getAttribute('src');
                var clone = sortableOrigin.el.querySelector('img[src="' + src + '"]');
                el && el.parentNode.removeChild(el);

                if (clone) {
                    var cloneItem = clone.closest('.draggable-item');
                    if (cloneItem.classList.contains('is-undraggable')) {
                        cloneItem.classList.remove('is-undraggable')
                    }
                }

                galleryCount();
            }
        });

        // Add button functionality
        jQuery('#gallery').prop('onclick',null).off('click').on('click', '.pwt-add-button', function () {
            jQuery(this).closest('.draggable-item').clone().appendTo(sortableResult.el);
            jQuery(this).closest('.draggable-item').addClass('is-undraggable');
            galleryCount();
        });
    }

    function galleryCount() {
        jQuery('#imageCount').html(jQuery('#js-sortable-result img').length);
    }

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
        choicesDestination.setValueByChoice('default');
        pwtImage.setDestination('#' + id + '_destinationFolder');
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
            }
        }

        return false;
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
                jQuery('#' + id + ' .js-pwt-image-targetfile').val(fileName);
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

        jQuery('#' + id + '_preview img').prop('src', file);
        jQuery('#' + id + '_js-pwtimage-image').prop('src', file);
        jQuery('#' + id + ' .js-pwt-image-localfile').val(file);

        // Make the edit tab visible
        jQuery('[href="#edit"]').trigger('click');

        // Hide the message
        jQuery('#' + id + ' .pwt-fulltab-message').addClass('is-hidden');
        jQuery('#' + id + ' .pwt-edit-block').removeClass('is-hidden');

        // Show the preview
        jQuery('#' + id + ' .js-image-preview').removeClass('is-hidden');

        // Set the folder option to select
        choicesDestination.setValueByChoice('select');
        pwtImage.setDestination('#' + id + '_destinationFolder');

        // Set the path of the image
        var basePath = jQuery('#' + id + '_selectFolder').prev().text(),
            path = file.substring(basePath.length, file.lastIndexOf('/')),
            filename = file.substring(file.lastIndexOf('/') + 1);

        var sourcePath = jQuery('#' + id + ' .js-sourcePath').text();

        if (path.lastIndexOf(sourcePath) === 0) {
            path = path.substring(sourcePath.length);
        }

        if (path.length > 1) {
            choicesFolder.setValueByChoice(path);
        }

        if (filename.length > 0) {
            jQuery('#' + id + ' #pwt-image-filename').val(filename);
        }

        // Enable the insert button on the edit page
        jQuery('#' + id + ' .js-button-image').prop('disabled', false);
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

    // Return the public parts
    return pwtImage;

}());

/*! choices.js v3.0.2 | (c) 2017 Josh Johnson | https://github.com/jshjohnson/Choices#readme */ 
!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.Choices=t():e.Choices=t()}(this,function(){return function(e){function t(n){if(i[n])return i[n].exports;var s=i[n]={exports:{},id:n,loaded:!1};return e[n].call(s.exports,s,s.exports,t),s.loaded=!0,s.exports}var i={};return t.m=e,t.c=i,t.p="/assets/scripts/dist/",t(0)}([function(e,t,i){e.exports=i(1)},function(e,t,i){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function s(e,t,i){return t in e?Object.defineProperty(e,t,{value:i,enumerable:!0,configurable:!0,writable:!0}):e[t]=i,e}function o(e){if(Array.isArray(e)){for(var t=0,i=Array(e.length);t<e.length;t++)i[t]=e[t];return i}return Array.from(e)}function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var a=function(){function e(e,t){for(var i=0;i<t.length;i++){var n=t[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,i,n){return i&&e(t.prototype,i),n&&e(t,n),t}}(),c=i(2),l=n(c),h=i(3),u=n(h),d=i(4),f=n(d),p=i(30),v=i(31);i(32);var m=function(){function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"[data-choice]",i=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};if(r(this,e),(0,v.isType)("String",t)){var n=document.querySelectorAll(t);if(n.length>1)for(var s=1;s<n.length;s++){var o=n[s];new e(o,i)}}var a={silent:!1,items:[],choices:[],renderChoiceLimit:-1,maxItemCount:-1,addItems:!0,removeItems:!0,removeItemButton:!1,editItems:!1,duplicateItems:!0,delimiter:",",paste:!0,searchEnabled:!0,searchChoices:!0,searchFloor:1,searchResultLimit:4,searchFields:["label","value"],position:"auto",resetScrollPosition:!0,regexFilter:null,shouldSort:!0,shouldSortItems:!1,sortFilter:v.sortByAlpha,placeholder:!0,placeholderValue:null,searchPlaceholderValue:null,prependValue:null,appendValue:null,renderSelectedChoices:"auto",loadingText:"Loading...",noResultsText:"No results found",noChoicesText:"No choices to choose from",itemSelectText:"Press to select",addItemText:function(e){return'Press Enter to add <b>"'+e+'"</b>'},maxItemText:function(e){return"Only "+e+" values can be added."},uniqueItemText:"Only unique values can be added.",classNames:{containerOuter:"choices",containerInner:"choices__inner",input:"choices__input",inputCloned:"choices__input--cloned",list:"choices__list",listItems:"choices__list--multiple",listSingle:"choices__list--single",listDropdown:"choices__list--dropdown",item:"choices__item",itemSelectable:"choices__item--selectable",itemDisabled:"choices__item--disabled",itemChoice:"choices__item--choice",placeholder:"choices__placeholder",group:"choices__group",groupHeading:"choices__heading",button:"choices__button",activeState:"is-active",focusState:"is-focused",openState:"is-open",disabledState:"is-disabled",highlightedState:"is-highlighted",hiddenState:"is-hidden",flippedState:"is-flipped",loadingState:"is-loading",noResults:"has-no-results",noChoices:"has-no-choices"},fuseOptions:{include:"score"},callbackOnInit:null,callbackOnCreateTemplates:null};if(this.idNames={itemChoice:"item-choice"},this.config=(0,v.extend)(a,i),"auto"!==this.config.renderSelectedChoices&&"always"!==this.config.renderSelectedChoices&&(this.config.silent||console.warn("renderSelectedChoices: Possible values are 'auto' and 'always'. Falling back to 'auto'."),this.config.renderSelectedChoices="auto"),this.store=new f.default(this.render),this.initialised=!1,this.currentState={},this.prevState={},this.currentValue="",this.element=t,this.passedElement=(0,v.isType)("String",t)?document.querySelector(t):t,!this.passedElement)return void(this.config.silent||console.error("Passed element not found"));this.isTextElement="text"===this.passedElement.type,this.isSelectOneElement="select-one"===this.passedElement.type,this.isSelectMultipleElement="select-multiple"===this.passedElement.type,this.isSelectElement=this.isSelectOneElement||this.isSelectMultipleElement,this.isValidElementType=this.isTextElement||this.isSelectElement,this.isIe11=!(!navigator.userAgent.match(/Trident/)||!navigator.userAgent.match(/rv[ :]11/)),this.isScrollingOnIe=!1,this.config.shouldSortItems===!0&&this.isSelectOneElement&&(this.config.silent||console.warn("shouldSortElements: Type of passed element is 'select-one', falling back to false.")),this.highlightPosition=0,this.canSearch=this.config.searchEnabled,this.placeholder=!1,this.isSelectOneElement||(this.placeholder=!!this.config.placeholder&&(this.config.placeholderValue||this.passedElement.getAttribute("placeholder"))),this.presetChoices=this.config.choices,this.presetItems=this.config.items,this.passedElement.value&&(this.presetItems=this.presetItems.concat(this.passedElement.value.split(this.config.delimiter))),this.baseId=(0,v.generateId)(this.passedElement,"choices-"),this.render=this.render.bind(this),this._onFocus=this._onFocus.bind(this),this._onBlur=this._onBlur.bind(this),this._onKeyUp=this._onKeyUp.bind(this),this._onKeyDown=this._onKeyDown.bind(this),this._onClick=this._onClick.bind(this),this._onTouchMove=this._onTouchMove.bind(this),this._onTouchEnd=this._onTouchEnd.bind(this),this._onMouseDown=this._onMouseDown.bind(this),this._onMouseOver=this._onMouseOver.bind(this),this._onPaste=this._onPaste.bind(this),this._onInput=this._onInput.bind(this),this.wasTap=!0;var c="classList"in document.documentElement;c||this.config.silent||console.error("Choices: Your browser doesn't support Choices");var l=(0,v.isElement)(this.passedElement)&&this.isValidElementType;if(l){if("active"===this.passedElement.getAttribute("data-choice"))return;this.init()}else this.config.silent||console.error("Incompatible input passed")}return a(e,[{key:"init",value:function(){if(this.initialised!==!0){var e=this.config.callbackOnInit;this.initialised=!0,this._createTemplates(),this._createInput(),this.store.subscribe(this.render),this.render(),this._addEventListeners(),e&&(0,v.isType)("Function",e)&&e.call(this)}}},{key:"destroy",value:function(){if(this.initialised!==!1){this._removeEventListeners(),this.passedElement.classList.remove(this.config.classNames.input,this.config.classNames.hiddenState),this.passedElement.removeAttribute("tabindex");var e=this.passedElement.getAttribute("data-choice-orig-style");Boolean(e)?(this.passedElement.removeAttribute("data-choice-orig-style"),this.passedElement.setAttribute("style",e)):this.passedElement.removeAttribute("style"),this.passedElement.removeAttribute("aria-hidden"),this.passedElement.removeAttribute("data-choice"),this.passedElement.value=this.passedElement.value,this.containerOuter.parentNode.insertBefore(this.passedElement,this.containerOuter),this.containerOuter.parentNode.removeChild(this.containerOuter),this.clearStore(),this.config.templates=null,this.initialised=!1}}},{key:"renderGroups",value:function(e,t,i){var n=this,s=i||document.createDocumentFragment(),o=this.config.sortFilter;return this.config.shouldSort&&e.sort(o),e.forEach(function(e){var i=t.filter(function(t){return n.isSelectOneElement?t.groupId===e.id:t.groupId===e.id&&!t.selected});if(i.length>=1){var o=n._getTemplate("choiceGroup",e);s.appendChild(o),n.renderChoices(i,s,!0)}}),s}},{key:"renderChoices",value:function(e,t){var i=this,n=arguments.length>2&&void 0!==arguments[2]&&arguments[2],s=t||document.createDocumentFragment(),r=this.config,a=r.renderSelectedChoices,c=r.searchResultLimit,l=r.renderChoiceLimit,h=this.isSearching?v.sortByScore:this.config.sortFilter,u=function(e){var t="auto"!==a||(i.isSelectOneElement||!e.selected);if(t){var n=i._getTemplate("choice",e);s.appendChild(n)}},d=e;"auto"!==a||this.isSelectOneElement||(d=e.filter(function(e){return!e.selected}));var f=d.reduce(function(e,t){return t.placeholder?e.placeholderChoices.push(t):e.normalChoices.push(t),e},{placeholderChoices:[],normalChoices:[]}),p=f.placeholderChoices,m=f.normalChoices;(this.config.shouldSort||this.isSearching)&&m.sort(h);var g=d.length,y=[].concat(o(p),o(m));this.isSearching?g=c:l>0&&!n&&(g=l);for(var b=0;b<g;b++)y[b]&&u(y[b]);return s}},{key:"renderItems",value:function(e){var t=this,i=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,n=i||document.createDocumentFragment();if(this.config.shouldSortItems&&!this.isSelectOneElement&&e.sort(this.config.sortFilter),this.isTextElement){var s=this.store.getItemsReducedToValues(e),o=s.join(this.config.delimiter);this.passedElement.setAttribute("value",o),this.passedElement.value=o}else{var r=document.createDocumentFragment();e.forEach(function(e){var i=t._getTemplate("option",e);r.appendChild(i)}),this.passedElement.innerHTML="",this.passedElement.appendChild(r)}return e.forEach(function(e){var i=t._getTemplate("item",e);n.appendChild(i)}),n}},{key:"render",value:function(){if(this.currentState=this.store.getState(),this.currentState!==this.prevState){if((this.currentState.choices!==this.prevState.choices||this.currentState.groups!==this.prevState.groups||this.currentState.items!==this.prevState.items)&&this.isSelectElement){var e=this.store.getGroupsFilteredByActive(),t=this.store.getChoicesFilteredByActive(),i=document.createDocumentFragment();this.choiceList.innerHTML="",this.config.resetScrollPosition&&(this.choiceList.scrollTop=0),e.length>=1&&this.isSearching!==!0?i=this.renderGroups(e,t,i):t.length>=1&&(i=this.renderChoices(t,i));var n=this.store.getItemsFilteredByActive(),s=this._canAddItem(n,this.input.value);if(i.childNodes&&i.childNodes.length>0)s.response?(this.choiceList.appendChild(i),this._highlightChoice()):this.choiceList.appendChild(this._getTemplate("notice",s.notice));else{var o=void 0,r=void 0;this.isSearching?(r=(0,v.isType)("Function",this.config.noResultsText)?this.config.noResultsText():this.config.noResultsText,o=this._getTemplate("notice",r,"no-results")):(r=(0,v.isType)("Function",this.config.noChoicesText)?this.config.noChoicesText():this.config.noChoicesText,o=this._getTemplate("notice",r,"no-choices")),this.choiceList.appendChild(o)}}if(this.currentState.items!==this.prevState.items){var a=this.store.getItemsFilteredByActive();if(this.itemList.innerHTML="",a&&a){var c=this.renderItems(a);c.childNodes&&this.itemList.appendChild(c)}}this.prevState=this.currentState}}},{key:"highlightItem",value:function(e){var t=!(arguments.length>1&&void 0!==arguments[1])||arguments[1];if(!e)return this;var i=e.id,n=e.groupId,s=n>=0?this.store.getGroupById(n):null;return this.store.dispatch((0,p.highlightItem)(i,!0)),t&&(s&&s.value?(0,v.triggerEvent)(this.passedElement,"highlightItem",{id:i,value:e.value,label:e.label,groupValue:s.value}):(0,v.triggerEvent)(this.passedElement,"highlightItem",{id:i,value:e.value,label:e.label})),this}},{key:"unhighlightItem",value:function(e){if(!e)return this;var t=e.id,i=e.groupId,n=i>=0?this.store.getGroupById(i):null;return this.store.dispatch((0,p.highlightItem)(t,!1)),n&&n.value?(0,v.triggerEvent)(this.passedElement,"unhighlightItem",{id:t,value:e.value,label:e.label,groupValue:n.value}):(0,v.triggerEvent)(this.passedElement,"unhighlightItem",{id:t,value:e.value,label:e.label}),this}},{key:"highlightAll",value:function(){var e=this,t=this.store.getItems();return t.forEach(function(t){e.highlightItem(t)}),this}},{key:"unhighlightAll",value:function(){var e=this,t=this.store.getItems();return t.forEach(function(t){e.unhighlightItem(t)}),this}},{key:"removeItemsByValue",value:function(e){var t=this;if(!e||!(0,v.isType)("String",e))return this;var i=this.store.getItemsFilteredByActive();return i.forEach(function(i){i.value===e&&t._removeItem(i)}),this}},{key:"removeActiveItems",value:function(e){var t=this,i=this.store.getItemsFilteredByActive();return i.forEach(function(i){i.active&&e!==i.id&&t._removeItem(i)}),this}},{key:"removeHighlightedItems",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]&&arguments[0],i=this.store.getItemsFilteredByActive();return i.forEach(function(i){i.highlighted&&i.active&&(e._removeItem(i),t&&e._triggerChange(i.value))}),this}},{key:"showDropdown",value:function(){var e=arguments.length>0&&void 0!==arguments[0]&&arguments[0],t=document.body,i=document.documentElement,n=Math.max(t.scrollHeight,t.offsetHeight,i.clientHeight,i.scrollHeight,i.offsetHeight);this.containerOuter.classList.add(this.config.classNames.openState),this.containerOuter.setAttribute("aria-expanded","true"),this.dropdown.classList.add(this.config.classNames.activeState),this.dropdown.setAttribute("aria-expanded","true");var s=this.dropdown.getBoundingClientRect(),o=Math.ceil(s.top+window.scrollY+this.dropdown.offsetHeight),r=!1;return"auto"===this.config.position?r=o>=n:"top"===this.config.position&&(r=!0),r&&this.containerOuter.classList.add(this.config.classNames.flippedState),e&&this.canSearch&&document.activeElement!==this.input&&this.input.focus(),(0,v.triggerEvent)(this.passedElement,"showDropdown",{}),this}},{key:"hideDropdown",value:function(){var e=arguments.length>0&&void 0!==arguments[0]&&arguments[0],t=this.containerOuter.classList.contains(this.config.classNames.flippedState);return this.containerOuter.classList.remove(this.config.classNames.openState),this.containerOuter.setAttribute("aria-expanded","false"),this.dropdown.classList.remove(this.config.classNames.activeState),this.dropdown.setAttribute("aria-expanded","false"),t&&this.containerOuter.classList.remove(this.config.classNames.flippedState),e&&this.canSearch&&document.activeElement===this.input&&this.input.blur(),(0,v.triggerEvent)(this.passedElement,"hideDropdown",{}),this}},{key:"toggleDropdown",value:function(){var e=this.dropdown.classList.contains(this.config.classNames.activeState);return e?this.hideDropdown():this.showDropdown(!0),this}},{key:"getValue",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]&&arguments[0],i=this.store.getItemsFilteredByActive(),n=[];return i.forEach(function(i){e.isTextElement?n.push(t?i.value:i):i.active&&n.push(t?i.value:i)}),this.isSelectOneElement?n[0]:n}},{key:"setValue",value:function(e){var t=this;if(this.initialised===!0){var i=[].concat(o(e)),n=function(e){var i=(0,v.getType)(e);if("Object"===i){if(!e.value)return;t.isTextElement?t._addItem(e.value,e.label,e.id,void 0,e.customProperties,e.placeholder):t._addChoice(e.value,e.label,!0,!1,-1,e.customProperties,e.placeholder)}else"String"===i&&(t.isTextElement?t._addItem(e):t._addChoice(e,e,!0,!1,-1,null))};i.length>1?i.forEach(function(e){n(e)}):n(i[0])}return this}},{key:"setValueByChoice",value:function(e){var t=this;if(!this.isTextElement){var i=this.store.getChoices(),n=(0,v.isType)("Array",e)?e:[e];n.forEach(function(e){var n=i.find(function(t){return t.value===e});n?n.selected?t.config.silent||console.warn("Attempting to select choice already selected"):t._addItem(n.value,n.label,n.id,n.groupId,n.customProperties,n.placeholder,n.keyCode):t.config.silent||console.warn("Attempting to select choice that does not exist")})}return this}},{key:"setChoices",value:function(e,t,i){var n=this,s=arguments.length>3&&void 0!==arguments[3]&&arguments[3];if(this.initialised===!0&&this.isSelectElement){if(!(0,v.isType)("Array",e)||!t)return this;s&&this._clearChoices(),e&&e.length&&(this.containerOuter.classList.remove(this.config.classNames.loadingState),e.forEach(function(e){e.choices?n._addGroup(e,e.id||null,t,i):n._addChoice(e[t],e[i],e.selected,e.disabled,void 0,e.customProperties,e.placeholder)}))}return this}},{key:"clearStore",value:function(){return this.store.dispatch((0,p.clearAll)()),this}},{key:"clearInput",value:function(){return this.input.value&&(this.input.value=""),this.isSelectOneElement||this._setInputWidth(),!this.isTextElement&&this.config.searchEnabled&&(this.isSearching=!1,this.store.dispatch((0,p.activateChoices)(!0))),this}},{key:"enable",value:function(){if(this.initialised){this.passedElement.disabled=!1;var e=this.containerOuter.classList.contains(this.config.classNames.disabledState);e&&(this._addEventListeners(),this.passedElement.removeAttribute("disabled"),this.input.removeAttribute("disabled"),this.containerOuter.classList.remove(this.config.classNames.disabledState),this.containerOuter.removeAttribute("aria-disabled"),this.isSelectOneElement&&this.containerOuter.setAttribute("tabindex","0"))}return this}},{key:"disable",value:function(){if(this.initialised){this.passedElement.disabled=!0;var e=!this.containerOuter.classList.contains(this.config.classNames.disabledState);e&&(this._removeEventListeners(),this.passedElement.setAttribute("disabled",""),this.input.setAttribute("disabled",""),this.containerOuter.classList.add(this.config.classNames.disabledState),this.containerOuter.setAttribute("aria-disabled","true"),this.isSelectOneElement&&this.containerOuter.setAttribute("tabindex","-1"))}return this}},{key:"ajax",value:function(e){var t=this;return this.initialised===!0&&this.isSelectElement&&(requestAnimationFrame(function(){t._handleLoadingState(!0)}),e(this._ajaxCallback())),this}},{key:"_triggerChange",value:function(e){e&&(0,v.triggerEvent)(this.passedElement,"change",{value:e})}},{key:"_handleButtonAction",value:function(e,t){if(e&&t&&this.config.removeItems&&this.config.removeItemButton){var i=t.parentNode.getAttribute("data-id"),n=e.find(function(e){return e.id===parseInt(i,10)});this._removeItem(n),this._triggerChange(n.value),this.isSelectOneElement&&this._selectPlaceholderChoice()}}},{key:"_selectPlaceholderChoice",value:function(){var e=this.store.getPlaceholderChoice();e&&(this._addItem(e.value,e.label,e.id,e.groupId,null,e.placeholder),this._triggerChange(e.value))}},{key:"_handleItemAction",value:function(e,t){var i=this,n=arguments.length>2&&void 0!==arguments[2]&&arguments[2];if(e&&t&&this.config.removeItems&&!this.isSelectOneElement){var s=t.getAttribute("data-id");e.forEach(function(e){e.id!==parseInt(s,10)||e.highlighted?n||e.highlighted&&i.unhighlightItem(e):i.highlightItem(e)}),document.activeElement!==this.input&&this.input.focus()}}},{key:"_handleChoiceAction",value:function(e,t){if(e&&t){var i=t.getAttribute("data-id"),n=this.store.getChoiceById(i),s=e[0]&&e[0].keyCode?e[0].keyCode:null,o=this.dropdown.classList.contains(this.config.classNames.activeState);if(n.keyCode=s,(0,v.triggerEvent)(this.passedElement,"choice",{choice:n}),n&&!n.selected&&!n.disabled){var r=this._canAddItem(e,n.value);r.response&&(this._addItem(n.value,n.label,n.id,n.groupId,n.customProperties,n.placeholder,n.keyCode),this._triggerChange(n.value))}this.clearInput(),o&&this.isSelectOneElement&&(this.hideDropdown(),this.containerOuter.focus())}}},{key:"_handleBackspace",value:function(e){if(this.config.removeItems&&e){var t=e[e.length-1],i=e.some(function(e){return e.highlighted});this.config.editItems&&!i&&t?(this.input.value=t.value,this._setInputWidth(),this._removeItem(t),this._triggerChange(t.value)):(i||this.highlightItem(t,!1),this.removeHighlightedItems(!0))}}},{key:"_canAddItem",value:function(e,t){var i=!0,n=(0,v.isType)("Function",this.config.addItemText)?this.config.addItemText(t):this.config.addItemText;(this.isSelectMultipleElement||this.isTextElement)&&this.config.maxItemCount>0&&this.config.maxItemCount<=e.length&&(i=!1,n=(0,v.isType)("Function",this.config.maxItemText)?this.config.maxItemText(this.config.maxItemCount):this.config.maxItemText),this.isTextElement&&this.config.addItems&&i&&this.config.regexFilter&&(i=this._regexFilter(t));var s=!e.some(function(e){return(0,v.isType)("String",t)?e.value===t.trim():e.value===t});return s||this.config.duplicateItems||this.isSelectOneElement||!i||(i=!1,n=(0,v.isType)("Function",this.config.uniqueItemText)?this.config.uniqueItemText(t):this.config.uniqueItemText),{response:i,notice:n}}},{key:"_handleLoadingState",value:function(){var e=!(arguments.length>0&&void 0!==arguments[0])||arguments[0],t=this.itemList.querySelector("."+this.config.classNames.placeholder);e?(this.containerOuter.classList.add(this.config.classNames.loadingState),this.containerOuter.setAttribute("aria-busy","true"),this.isSelectOneElement?t?t.innerHTML=this.config.loadingText:(t=this._getTemplate("placeholder",this.config.loadingText),this.itemList.appendChild(t)):this.input.placeholder=this.config.loadingText):(this.containerOuter.classList.remove(this.config.classNames.loadingState),this.isSelectOneElement?t.innerHTML=this.placeholder||"":this.input.placeholder=this.placeholder||"")}},{key:"_ajaxCallback",value:function(){var e=this;return function(t,i,n){if(t&&i){var s=(0,v.isType)("Object",t)?[t]:t;s&&(0,v.isType)("Array",s)&&s.length?(e._handleLoadingState(!1),s.forEach(function(t){if(t.choices){var s=t.id||null;e._addGroup(t,s,i,n)}else e._addChoice(t[i],t[n],t.selected,t.disabled,void 0,t.customProperties,t.placeholder)}),e.isSelectOneElement&&e._selectPlaceholderChoice()):e._handleLoadingState(!1),e.containerOuter.removeAttribute("aria-busy")}}}},{key:"_searchChoices",value:function(e){var t=(0,v.isType)("String",e)?e.trim():e,i=(0,v.isType)("String",this.currentValue)?this.currentValue.trim():this.currentValue;if(t.length>=1&&t!==i+" "){var n=this.store.getSearchableChoices(),s=t,o=(0,v.isType)("Array",this.config.searchFields)?this.config.searchFields:[this.config.searchFields],r=Object.assign(this.config.fuseOptions,{keys:o}),a=new l.default(n,r),c=a.search(s);return this.currentValue=t,this.highlightPosition=0,this.isSearching=!0,this.store.dispatch((0,p.filterChoices)(c)),c.length}return 0}},{key:"_handleSearch",value:function(e){if(e){var t=this.store.getChoices(),i=t.some(function(e){return!e.active});if(this.input===document.activeElement)if(e&&e.length>=this.config.searchFloor){var n=0;this.config.searchChoices&&(n=this._searchChoices(e)),(0,v.triggerEvent)(this.passedElement,"search",{value:e,resultCount:n})}else i&&(this.isSearching=!1,this.store.dispatch((0,p.activateChoices)(!0)))}}},{key:"_addEventListeners",value:function(){document.addEventListener("keyup",this._onKeyUp),document.addEventListener("keydown",this._onKeyDown),document.addEventListener("click",this._onClick),document.addEventListener("touchmove",this._onTouchMove),document.addEventListener("touchend",this._onTouchEnd),document.addEventListener("mousedown",this._onMouseDown),document.addEventListener("mouseover",this._onMouseOver),this.isSelectOneElement&&(this.containerOuter.addEventListener("focus",this._onFocus),this.containerOuter.addEventListener("blur",this._onBlur)),this.input.addEventListener("input",this._onInput),this.input.addEventListener("paste",this._onPaste),this.input.addEventListener("focus",this._onFocus),this.input.addEventListener("blur",this._onBlur)}},{key:"_removeEventListeners",value:function(){document.removeEventListener("keyup",this._onKeyUp),document.removeEventListener("keydown",this._onKeyDown),document.removeEventListener("click",this._onClick),document.removeEventListener("touchmove",this._onTouchMove),document.removeEventListener("touchend",this._onTouchEnd),document.removeEventListener("mousedown",this._onMouseDown),document.removeEventListener("mouseover",this._onMouseOver),this.isSelectOneElement&&(this.containerOuter.removeEventListener("focus",this._onFocus),this.containerOuter.removeEventListener("blur",this._onBlur)),this.input.removeEventListener("input",this._onInput),this.input.removeEventListener("paste",this._onPaste),this.input.removeEventListener("focus",this._onFocus),this.input.removeEventListener("blur",this._onBlur)}},{key:"_setInputWidth",value:function(){this.placeholder?this.input.value&&this.input.value.length>=this.placeholder.length/1.25&&(this.input.style.width=(0,v.getWidthOfInput)(this.input)):this.input.style.width=(0,v.getWidthOfInput)(this.input)}},{key:"_onKeyDown",value:function(e){var t,i=this;if(e.target===this.input||this.containerOuter.contains(e.target)){var n=e.target,o=this.store.getItemsFilteredByActive(),r=this.input===document.activeElement,a=this.dropdown.classList.contains(this.config.classNames.activeState),c=this.itemList&&this.itemList.children,l=String.fromCharCode(e.keyCode),h=46,u=8,d=13,f=65,p=27,m=38,g=40,y=33,b=34,E=e.ctrlKey||e.metaKey;this.isTextElement||!/[a-zA-Z0-9-_ ]/.test(l)||a||this.showDropdown(!0),this.canSearch=this.config.searchEnabled;var _=function(){E&&c&&(i.canSearch=!1,i.config.removeItems&&!i.input.value&&i.input===document.activeElement&&i.highlightAll())},S=function(){if(i.isTextElement&&n.value){var t=i.input.value,s=i._canAddItem(o,t);s.response&&(a&&i.hideDropdown(),i._addItem(t),i._triggerChange(t),i.clearInput())}if(n.hasAttribute("data-button")&&(i._handleButtonAction(o,n),e.preventDefault()),a){e.preventDefault();var r=i.dropdown.querySelector("."+i.config.classNames.highlightedState);r&&(o[0]&&(o[0].keyCode=d),i._handleChoiceAction(o,r))}else i.isSelectOneElement&&(a||(i.showDropdown(!0),e.preventDefault()))},I=function(){a&&(i.toggleDropdown(),i.containerOuter.focus())},w=function(){if(a||i.isSelectOneElement){a||i.showDropdown(!0),i.canSearch=!1;var t=e.keyCode===g||e.keyCode===b?1:-1,n=e.metaKey||e.keyCode===b||e.keyCode===y,s=void 0;if(n)s=t>0?Array.from(i.dropdown.querySelectorAll("[data-choice-selectable]")).pop():i.dropdown.querySelector("[data-choice-selectable]");else{var o=i.dropdown.querySelector("."+i.config.classNames.highlightedState);s=o?(0,v.getAdjacentEl)(o,"[data-choice-selectable]",t):i.dropdown.querySelector("[data-choice-selectable]")}s&&((0,v.isScrolledIntoView)(s,i.choiceList,t)||i._scrollToChoice(s,t),i._highlightChoice(s)),e.preventDefault()}},T=function(){!r||e.target.value||i.isSelectOneElement||(i._handleBackspace(o),e.preventDefault())},C=(t={},s(t,f,_),s(t,d,S),s(t,p,I),s(t,m,w),s(t,y,w),s(t,g,w),s(t,b,w),s(t,u,T),s(t,h,T),t);C[e.keyCode]&&C[e.keyCode]()}}},{key:"_onKeyUp",value:function(e){if(e.target===this.input){var t=this.input.value,i=this.store.getItemsFilteredByActive(),n=this._canAddItem(i,t);if(this.isTextElement){var s=this.dropdown.classList.contains(this.config.classNames.activeState);if(t){if(n.notice){var o=this._getTemplate("notice",n.notice);this.dropdown.innerHTML=o.outerHTML}n.response===!0?s||this.showDropdown():!n.notice&&s&&this.hideDropdown()}else s&&this.hideDropdown()}else{var r=46,a=8;e.keyCode!==r&&e.keyCode!==a||e.target.value?this.canSearch&&n.response&&this._handleSearch(this.input.value):!this.isTextElement&&this.isSearching&&(this.isSearching=!1,this.store.dispatch((0,p.activateChoices)(!0)))}this.canSearch=this.config.searchEnabled}}},{key:"_onInput",value:function(){this.isSelectOneElement||this._setInputWidth()}},{key:"_onTouchMove",value:function(){this.wasTap===!0&&(this.wasTap=!1)}},{key:"_onTouchEnd",value:function(e){var t=e.target||e.touches[0].target,i=this.dropdown.classList.contains(this.config.classNames.activeState);this.wasTap===!0&&this.containerOuter.contains(t)&&(t!==this.containerOuter&&t!==this.containerInner||this.isSelectOneElement||(this.isTextElement?document.activeElement!==this.input&&this.input.focus():i||this.showDropdown(!0)),e.stopPropagation()),this.wasTap=!0}},{key:"_onMouseDown",value:function(e){var t=e.target;if(t===this.choiceList&&this.isIe11&&(this.isScrollingOnIe=!0),this.containerOuter.contains(t)&&t!==this.input){var i=void 0,n=this.store.getItemsFilteredByActive(),s=e.shiftKey;(i=(0,v.findAncestorByAttrName)(t,"data-button"))?this._handleButtonAction(n,i):(i=(0,v.findAncestorByAttrName)(t,"data-item"))?this._handleItemAction(n,i,s):(i=(0,v.findAncestorByAttrName)(t,"data-choice"))&&this._handleChoiceAction(n,i),e.preventDefault()}}},{key:"_onClick",value:function(e){var t=e.target,i=this.dropdown.classList.contains(this.config.classNames.activeState),n=this.store.getItemsFilteredByActive();if(this.containerOuter.contains(t))t.hasAttribute("data-button")&&this._handleButtonAction(n,t),i?this.isSelectOneElement&&t!==this.input&&!this.dropdown.contains(t)&&this.hideDropdown(!0):this.isTextElement?document.activeElement!==this.input&&this.input.focus():this.canSearch?this.showDropdown(!0):(this.showDropdown(),this.containerOuter.focus());else{var s=n.some(function(e){return e.highlighted});s&&this.unhighlightAll(),this.containerOuter.classList.remove(this.config.classNames.focusState),i&&this.hideDropdown()}}},{key:"_onMouseOver",value:function(e){(e.target===this.dropdown||this.dropdown.contains(e.target))&&e.target.hasAttribute("data-choice")&&this._highlightChoice(e.target)}},{key:"_onPaste",value:function(e){e.target!==this.input||this.config.paste||e.preventDefault()}},{key:"_onFocus",value:function(e){var t=this,i=e.target;if(this.containerOuter.contains(i)){var n=this.dropdown.classList.contains(this.config.classNames.activeState),s={text:function(){i===t.input&&t.containerOuter.classList.add(t.config.classNames.focusState)},"select-one":function(){t.containerOuter.classList.add(t.config.classNames.focusState),i===t.input&&(n||t.showDropdown())},"select-multiple":function(){i===t.input&&(t.containerOuter.classList.add(t.config.classNames.focusState),n||t.showDropdown(!0))}};s[this.passedElement.type]()}}},{key:"_onBlur",value:function(e){var t=this,i=e.target;if(this.containerOuter.contains(i)&&!this.isScrollingOnIe){var n=this.store.getItemsFilteredByActive(),s=this.dropdown.classList.contains(this.config.classNames.activeState),o=n.some(function(e){return e.highlighted}),r={text:function(){i===t.input&&(t.containerOuter.classList.remove(t.config.classNames.focusState),o&&t.unhighlightAll(),s&&t.hideDropdown())},"select-one":function(){t.containerOuter.classList.remove(t.config.classNames.focusState),i===t.containerOuter&&s&&!t.canSearch&&t.hideDropdown(),i===t.input&&s&&t.hideDropdown()},"select-multiple":function(){i===t.input&&(t.containerOuter.classList.remove(t.config.classNames.focusState),s&&t.hideDropdown(),o&&t.unhighlightAll())}};r[this.passedElement.type]()}else this.isScrollingOnIe=!1,this.input.focus()}},{key:"_regexFilter",value:function(e){if(!e)return!1;var t=this.config.regexFilter,i=new RegExp(t.source,"i");return i.test(e)}},{key:"_scrollToChoice",value:function(e,t){var i=this;if(e){var n=this.choiceList.offsetHeight,s=e.offsetHeight,o=e.offsetTop+s,r=this.choiceList.scrollTop+n,a=t>0?this.choiceList.scrollTop+o-r:e.offsetTop,c=function e(){var n=4,s=i.choiceList.scrollTop,o=!1,r=void 0,c=void 0;t>0?(r=(a-s)/n,c=r>1?r:1,i.choiceList.scrollTop=s+c,s<a&&(o=!0)):(r=(s-a)/n,c=r>1?r:1,i.choiceList.scrollTop=s-c,s>a&&(o=!0)),o&&requestAnimationFrame(function(i){e(i,a,t)})};requestAnimationFrame(function(e){c(e,a,t)})}}},{key:"_highlightChoice",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,i=Array.from(this.dropdown.querySelectorAll("[data-choice-selectable]")),n=t;if(i&&i.length){var s=Array.from(this.dropdown.querySelectorAll("."+this.config.classNames.highlightedState));s.forEach(function(t){t.classList.remove(e.config.classNames.highlightedState),t.setAttribute("aria-selected","false")}),n?this.highlightPosition=i.indexOf(n):(n=i.length>this.highlightPosition?i[this.highlightPosition]:i[i.length-1],n||(n=i[0])),n.classList.add(this.config.classNames.highlightedState),n.setAttribute("aria-selected","true"),this.containerOuter.setAttribute("aria-activedescendant",n.id)}}},{key:"_addItem",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:-1,n=arguments.length>3&&void 0!==arguments[3]?arguments[3]:-1,s=arguments.length>4&&void 0!==arguments[4]?arguments[4]:null,o=arguments.length>5&&void 0!==arguments[5]&&arguments[5],r=arguments.length>6&&void 0!==arguments[6]?arguments[6]:null,a=(0,v.isType)("String",e)?e.trim():e,c=r,l=this.store.getItems(),h=t||a,u=parseInt(i,10)||-1,d=n>=0?this.store.getGroupById(n):null,f=l?l.length+1:1;return this.config.prependValue&&(a=this.config.prependValue+a.toString()),this.config.appendValue&&(a+=this.config.appendValue.toString()),this.store.dispatch((0,p.addItem)(a,h,f,u,n,s,o,c)),this.isSelectOneElement&&this.removeActiveItems(f),d&&d.value?(0,v.triggerEvent)(this.passedElement,"addItem",{
id:f,value:a,label:h,groupValue:d.value,keyCode:c}):(0,v.triggerEvent)(this.passedElement,"addItem",{id:f,value:a,label:h,keyCode:c}),this}},{key:"_removeItem",value:function(e){if(!e||!(0,v.isType)("Object",e))return this;var t=e.id,i=e.value,n=e.label,s=e.choiceId,o=e.groupId,r=o>=0?this.store.getGroupById(o):null;return this.store.dispatch((0,p.removeItem)(t,s)),r&&r.value?(0,v.triggerEvent)(this.passedElement,"removeItem",{id:t,value:i,label:n,groupValue:r.value}):(0,v.triggerEvent)(this.passedElement,"removeItem",{id:t,value:i,label:n}),this}},{key:"_addChoice",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,i=arguments.length>2&&void 0!==arguments[2]&&arguments[2],n=arguments.length>3&&void 0!==arguments[3]&&arguments[3],s=arguments.length>4&&void 0!==arguments[4]?arguments[4]:-1,o=arguments.length>5&&void 0!==arguments[5]?arguments[5]:null,r=arguments.length>6&&void 0!==arguments[6]&&arguments[6],a=arguments.length>7&&void 0!==arguments[7]?arguments[7]:null;if("undefined"!=typeof e&&null!==e){var c=this.store.getChoices(),l=t||e,h=c?c.length+1:1,u=this.baseId+"-"+this.idNames.itemChoice+"-"+h;this.store.dispatch((0,p.addChoice)(e,l,h,s,n,u,o,r,a)),i&&this._addItem(e,l,h,void 0,o,r,a)}}},{key:"_clearChoices",value:function(){this.store.dispatch((0,p.clearChoices)())}},{key:"_addGroup",value:function(e,t){var i=this,n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"value",s=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"label",o=(0,v.isType)("Object",e)?e.choices:Array.from(e.getElementsByTagName("OPTION")),r=t?t:Math.floor((new Date).valueOf()*Math.random()),a=!!e.disabled&&e.disabled;o?(this.store.dispatch((0,p.addGroup)(e.label,r,!0,a)),o.forEach(function(e){var t=e.disabled||e.parentNode&&e.parentNode.disabled;i._addChoice(e[n],(0,v.isType)("Object",e)?e[s]:e.innerHTML,e.selected,t,r,e.customProperties,e.placeholder)})):this.store.dispatch((0,p.addGroup)(e.label,e.id,!1,e.disabled))}},{key:"_getTemplate",value:function(e){if(!e)return null;for(var t=this.config.templates,i=arguments.length,n=Array(i>1?i-1:0),s=1;s<i;s++)n[s-1]=arguments[s];return t[e].apply(t,n)}},{key:"_createTemplates",value:function(){var e=this,t=this.config.classNames,i={containerOuter:function(i){return(0,v.strToEl)('\n          <div\n            class="'+t.containerOuter+'"\n            '+(e.isSelectElement?e.config.searchEnabled?'role="combobox" aria-autocomplete="list"':'role="listbox"':"")+'\n            data-type="'+e.passedElement.type+'"\n            '+(e.isSelectOneElement?'tabindex="0"':"")+'\n            aria-haspopup="true"\n            aria-expanded="false"\n            dir="'+i+'"\n            >\n          </div>\n        ')},containerInner:function(){return(0,v.strToEl)('\n          <div class="'+t.containerInner+'"></div>\n        ')},itemList:function(){var i,n=(0,u.default)(t.list,(i={},s(i,t.listSingle,e.isSelectOneElement),s(i,t.listItems,!e.isSelectOneElement),i));return(0,v.strToEl)('\n          <div class="'+n+'"></div>\n        ')},placeholder:function(e){return(0,v.strToEl)('\n          <div class="'+t.placeholder+'">\n            '+e+"\n          </div>\n        ")},item:function(i){var n,o=(0,u.default)(t.item,(n={},s(n,t.highlightedState,i.highlighted),s(n,t.itemSelectable,!i.highlighted),s(n,t.placeholder,i.placeholder),n));if(e.config.removeItemButton){var r;return o=(0,u.default)(t.item,(r={},s(r,t.highlightedState,i.highlighted),s(r,t.itemSelectable,!i.disabled),s(r,t.placeholder,i.placeholder),r)),(0,v.strToEl)('\n            <div\n              class="'+o+'"\n              data-item\n              data-id="'+i.id+'"\n              data-value="'+i.value+'"\n              data-deletable\n              '+(i.active?'aria-selected="true"':"")+"\n              "+(i.disabled?'aria-disabled="true"':"")+"\n              >\n              "+i.label+'<!--\n           --><button\n                type="button"\n                class="'+t.button+'"\n                data-button\n                aria-label="Remove item: \''+i.value+"'\"\n                >\n                Remove item\n              </button>\n            </div>\n          ")}return(0,v.strToEl)('\n          <div\n            class="'+o+'"\n            data-item\n            data-id="'+i.id+'"\n            data-value="'+i.value+'"\n            '+(i.active?'aria-selected="true"':"")+"\n            "+(i.disabled?'aria-disabled="true"':"")+"\n            >\n            "+i.label+"\n          </div>\n        ")},choiceList:function(){return(0,v.strToEl)('\n          <div\n            class="'+t.list+'"\n            dir="ltr"\n            role="listbox"\n            '+(e.isSelectOneElement?"":'aria-multiselectable="true"')+"\n            >\n          </div>\n        ")},choiceGroup:function(e){var i=(0,u.default)(t.group,s({},t.itemDisabled,e.disabled));return(0,v.strToEl)('\n          <div\n            class="'+i+'"\n            data-group\n            data-id="'+e.id+'"\n            data-value="'+e.value+'"\n            role="group"\n            '+(e.disabled?'aria-disabled="true"':"")+'\n            >\n            <div class="'+t.groupHeading+'">'+e.value+"</div>\n          </div>\n        ")},choice:function(i){var n,o=(0,u.default)(t.item,t.itemChoice,(n={},s(n,t.itemDisabled,i.disabled),s(n,t.itemSelectable,!i.disabled),s(n,t.placeholder,i.placeholder),n));return(0,v.strToEl)('\n          <div\n            class="'+o+'"\n            data-select-text="'+e.config.itemSelectText+'"\n            data-choice\n            data-id="'+i.id+'"\n            data-value="'+i.value+'"\n            '+(i.disabled?'data-choice-disabled aria-disabled="true"':"data-choice-selectable")+'\n            id="'+i.elementId+'"\n            '+(i.groupId>0?'role="treeitem"':'role="option"')+"\n            >\n            "+i.label+"\n          </div>\n        ")},input:function(){var e=(0,u.default)(t.input,t.inputCloned);return(0,v.strToEl)('\n          <input\n            type="text"\n            class="'+e+'"\n            autocomplete="off"\n            autocapitalize="off"\n            spellcheck="false"\n            role="textbox"\n            aria-autocomplete="list"\n            >\n        ')},dropdown:function(){var e=(0,u.default)(t.list,t.listDropdown);return(0,v.strToEl)('\n          <div\n            class="'+e+'"\n            aria-expanded="false"\n            >\n          </div>\n        ')},notice:function(e){var i,n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",o=(0,u.default)(t.item,t.itemChoice,(i={},s(i,t.noResults,"no-results"===n),s(i,t.noChoices,"no-choices"===n),i));return(0,v.strToEl)('\n          <div class="'+o+'">\n            '+e+"\n          </div>\n        ")},option:function(e){return(0,v.strToEl)('\n          <option value="'+e.value+'" selected>'+e.label+"</option>\n        ")}},n=this.config.callbackOnCreateTemplates,o={};n&&(0,v.isType)("Function",n)&&(o=n.call(this,v.strToEl)),this.config.templates=(0,v.extend)(i,o)}},{key:"_createInput",value:function(){var e=this,t=this.passedElement.getAttribute("dir")||"ltr",i=this._getTemplate("containerOuter",t),n=this._getTemplate("containerInner"),s=this._getTemplate("itemList"),o=this._getTemplate("choiceList"),r=this._getTemplate("input"),a=this._getTemplate("dropdown");this.containerOuter=i,this.containerInner=n,this.input=r,this.choiceList=o,this.itemList=s,this.dropdown=a,this.passedElement.classList.add(this.config.classNames.input,this.config.classNames.hiddenState),this.passedElement.tabIndex="-1";var c=this.passedElement.getAttribute("style");if(Boolean(c)&&this.passedElement.setAttribute("data-choice-orig-style",c),this.passedElement.setAttribute("style","display:none;"),this.passedElement.setAttribute("aria-hidden","true"),this.passedElement.setAttribute("data-choice","active"),(0,v.wrap)(this.passedElement,n),(0,v.wrap)(n,i),this.isSelectOneElement?r.placeholder=this.config.searchPlaceholderValue||"":this.placeholder&&(r.placeholder=this.placeholder,r.style.width=(0,v.getWidthOfInput)(r)),this.config.addItems||this.disable(),i.appendChild(n),i.appendChild(a),n.appendChild(s),this.isTextElement||a.appendChild(o),this.isSelectMultipleElement||this.isTextElement?n.appendChild(r):this.canSearch&&a.insertBefore(r,a.firstChild),this.isSelectElement){var l=Array.from(this.passedElement.getElementsByTagName("OPTGROUP"));if(this.highlightPosition=0,this.isSearching=!1,l&&l.length)l.forEach(function(t){e._addGroup(t,t.id||null)});else{var h=Array.from(this.passedElement.options),u=this.config.sortFilter,d=this.presetChoices;h.forEach(function(e){d.push({value:e.value,label:e.innerHTML,selected:e.selected,disabled:e.disabled||e.parentNode.disabled,placeholder:e.hasAttribute("placeholder")})}),this.config.shouldSort&&d.sort(u);var f=d.some(function(e){return e.selected});d.forEach(function(t,i){if(e.isSelectOneElement){var n=f||!f&&i>0;e._addChoice(t.value,t.label,!n||t.selected,!!n&&t.disabled,void 0,t.customProperties,t.placeholder)}else e._addChoice(t.value,t.label,t.selected,t.disabled,void 0,t.customProperties,t.placeholder)})}}else this.isTextElement&&this.presetItems.forEach(function(t){var i=(0,v.getType)(t);if("Object"===i){if(!t.value)return;e._addItem(t.value,t.label,t.id,void 0,t.customProperties,t.placeholder)}else"String"===i&&e._addItem(t)})}}]),e}();e.exports=m},function(e,t,i){!function(t){"use strict";function i(){console.log.apply(console,arguments)}function n(e,t){var i;this.list=e,this.options=t=t||{};for(i in a)a.hasOwnProperty(i)&&("boolean"==typeof a[i]?this.options[i]=i in t?t[i]:a[i]:this.options[i]=t[i]||a[i])}function s(e,t,i){var n,r,a,c,l,h;if(t){if(a=t.indexOf("."),a!==-1?(n=t.slice(0,a),r=t.slice(a+1)):n=t,c=e[n],null!==c&&void 0!==c)if(r||"string"!=typeof c&&"number"!=typeof c)if(o(c))for(l=0,h=c.length;l<h;l++)s(c[l],r,i);else r&&s(c,r,i);else i.push(c)}else i.push(e);return i}function o(e){return"[object Array]"===Object.prototype.toString.call(e)}function r(e,t){t=t||{},this.options=t,this.options.location=t.location||r.defaultOptions.location,this.options.distance="distance"in t?t.distance:r.defaultOptions.distance,this.options.threshold="threshold"in t?t.threshold:r.defaultOptions.threshold,this.options.maxPatternLength=t.maxPatternLength||r.defaultOptions.maxPatternLength,this.pattern=t.caseSensitive?e:e.toLowerCase(),this.patternLen=e.length,this.patternLen<=this.options.maxPatternLength&&(this.matchmask=1<<this.patternLen-1,this.patternAlphabet=this._calculatePatternAlphabet())}var a={id:null,caseSensitive:!1,include:[],shouldSort:!0,searchFn:r,sortFn:function(e,t){return e.score-t.score},getFn:s,keys:[],verbose:!1,tokenize:!1,matchAllTokens:!1,tokenSeparator:/ +/g,minMatchCharLength:1,findAllMatches:!1};n.VERSION="2.7.3",n.prototype.set=function(e){return this.list=e,e},n.prototype.search=function(e){this.options.verbose&&i("\nSearch term:",e,"\n"),this.pattern=e,this.results=[],this.resultMap={},this._keyMap=null,this._prepareSearchers(),this._startSearch(),this._computeScore(),this._sort();var t=this._format();return t},n.prototype._prepareSearchers=function(){var e=this.options,t=this.pattern,i=e.searchFn,n=t.split(e.tokenSeparator),s=0,o=n.length;if(this.options.tokenize)for(this.tokenSearchers=[];s<o;s++)this.tokenSearchers.push(new i(n[s],e));this.fullSeacher=new i(t,e)},n.prototype._startSearch=function(){var e,t,i,n,s=this.options,o=s.getFn,r=this.list,a=r.length,c=this.options.keys,l=c.length,h=null;if("string"==typeof r[0])for(i=0;i<a;i++)this._analyze("",r[i],i,i);else for(this._keyMap={},i=0;i<a;i++)for(h=r[i],n=0;n<l;n++){if(e=c[n],"string"!=typeof e){if(t=1-e.weight||1,this._keyMap[e.name]={weight:t},e.weight<=0||e.weight>1)throw new Error("Key weight has to be > 0 and <= 1");e=e.name}else this._keyMap[e]={weight:1};this._analyze(e,o(h,e,[]),h,i)}},n.prototype._analyze=function(e,t,n,s){var r,a,c,l,h,u,d,f,p,v,m,g,y,b,E,_=this.options,S=!1;if(void 0!==t&&null!==t){a=[];var I=0;if("string"==typeof t){if(r=t.split(_.tokenSeparator),_.verbose&&i("---------\nKey:",e),this.options.tokenize){for(b=0;b<this.tokenSearchers.length;b++){for(f=this.tokenSearchers[b],_.verbose&&i("Pattern:",f.pattern),p=[],g=!1,E=0;E<r.length;E++){v=r[E],m=f.search(v);var w={};m.isMatch?(w[v]=m.score,S=!0,g=!0,a.push(m.score)):(w[v]=1,this.options.matchAllTokens||a.push(1)),p.push(w)}g&&I++,_.verbose&&i("Token scores:",p)}for(l=a[0],u=a.length,b=1;b<u;b++)l+=a[b];l/=u,_.verbose&&i("Token score average:",l)}d=this.fullSeacher.search(t),_.verbose&&i("Full text score:",d.score),h=d.score,void 0!==l&&(h=(h+l)/2),_.verbose&&i("Score average:",h),y=!this.options.tokenize||!this.options.matchAllTokens||I>=this.tokenSearchers.length,_.verbose&&i("Check Matches",y),(S||d.isMatch)&&y&&(c=this.resultMap[s],c?c.output.push({key:e,score:h,matchedIndices:d.matchedIndices}):(this.resultMap[s]={item:n,output:[{key:e,score:h,matchedIndices:d.matchedIndices}]},this.results.push(this.resultMap[s])))}else if(o(t))for(b=0;b<t.length;b++)this._analyze(e,t[b],n,s)}},n.prototype._computeScore=function(){var e,t,n,s,o,r,a,c,l,h=this._keyMap,u=this.results;for(this.options.verbose&&i("\n\nComputing score:\n"),e=0;e<u.length;e++){for(n=0,s=u[e].output,o=s.length,c=1,t=0;t<o;t++)r=s[t].score,a=h?h[s[t].key].weight:1,l=r*a,1!==a?c=Math.min(c,l):(n+=l,s[t].nScore=l);1===c?u[e].score=n/o:u[e].score=c,this.options.verbose&&i(u[e])}},n.prototype._sort=function(){var e=this.options;e.shouldSort&&(e.verbose&&i("\n\nSorting...."),this.results.sort(e.sortFn))},n.prototype._format=function(){var e,t,n,s,o=this.options,r=o.getFn,a=[],c=this.results,l=o.include;for(o.verbose&&i("\n\nOutput:\n\n",c),n=o.id?function(e){c[e].item=r(c[e].item,o.id,[])[0]}:function(){},s=function(e){var t,i,n,s,o,r=c[e];if(l.length>0){if(t={item:r.item},l.indexOf("matches")!==-1)for(n=r.output,t.matches=[],i=0;i<n.length;i++)s=n[i],o={indices:s.matchedIndices},s.key&&(o.key=s.key),t.matches.push(o);l.indexOf("score")!==-1&&(t.score=c[e].score)}else t=r.item;return t},e=0,t=c.length;e<t;e++)n(e),a.push(s(e));return a},r.defaultOptions={location:0,distance:100,threshold:.6,maxPatternLength:32},r.prototype._calculatePatternAlphabet=function(){var e={},t=0;for(t=0;t<this.patternLen;t++)e[this.pattern.charAt(t)]=0;for(t=0;t<this.patternLen;t++)e[this.pattern.charAt(t)]|=1<<this.pattern.length-t-1;return e},r.prototype._bitapScore=function(e,t){var i=e/this.patternLen,n=Math.abs(this.options.location-t);return this.options.distance?i+n/this.options.distance:n?1:i},r.prototype.search=function(e){var t,i,n,s,o,r,a,c,l,h,u,d,f,p,v,m,g,y,b,E,_,S,I,w=this.options;if(e=w.caseSensitive?e:e.toLowerCase(),this.pattern===e)return{isMatch:!0,score:0,matchedIndices:[[0,e.length-1]]};if(this.patternLen>w.maxPatternLength){if(y=e.match(new RegExp(this.pattern.replace(w.tokenSeparator,"|"))),b=!!y)for(_=[],t=0,S=y.length;t<S;t++)I=y[t],_.push([e.indexOf(I),I.length-1]);return{isMatch:b,score:b?.5:1,matchedIndices:_}}for(s=w.findAllMatches,o=w.location,n=e.length,r=w.threshold,a=e.indexOf(this.pattern,o),E=[],t=0;t<n;t++)E[t]=0;for(a!=-1&&(r=Math.min(this._bitapScore(0,a),r),a=e.lastIndexOf(this.pattern,o+this.patternLen),a!=-1&&(r=Math.min(this._bitapScore(0,a),r))),a=-1,m=1,g=[],h=this.patternLen+n,t=0;t<this.patternLen;t++){for(c=0,l=h;c<l;)this._bitapScore(t,o+l)<=r?c=l:h=l,l=Math.floor((h-c)/2+c);for(h=l,u=Math.max(1,o-l+1),d=s?n:Math.min(o+l,n)+this.patternLen,f=Array(d+2),f[d+1]=(1<<t)-1,i=d;i>=u;i--)if(v=this.patternAlphabet[e.charAt(i-1)],v&&(E[i-1]=1),f[i]=(f[i+1]<<1|1)&v,0!==t&&(f[i]|=(p[i+1]|p[i])<<1|1|p[i+1]),f[i]&this.matchmask&&(m=this._bitapScore(t,i-1),m<=r)){if(r=m,a=i-1,g.push(a),a<=o)break;u=Math.max(1,2*o-a)}if(this._bitapScore(t+1,o)>r)break;p=f}return _=this._getMatchedIndices(E),{isMatch:a>=0,score:0===m?.001:m,matchedIndices:_}},r.prototype._getMatchedIndices=function(e){for(var t,i=[],n=-1,s=-1,o=0,r=e.length;o<r;o++)t=e[o],t&&n===-1?n=o:t||n===-1||(s=o-1,s-n+1>=this.options.minMatchCharLength&&i.push([n,s]),n=-1);return e[o-1]&&o-1-n+1>=this.options.minMatchCharLength&&i.push([n,o-1]),i},e.exports=n}(this)},function(e,t,i){var n,s;!function(){"use strict";function i(){for(var e=[],t=0;t<arguments.length;t++){var n=arguments[t];if(n){var s=typeof n;if("string"===s||"number"===s)e.push(n);else if(Array.isArray(n))e.push(i.apply(null,n));else if("object"===s)for(var r in n)o.call(n,r)&&n[r]&&e.push(r)}}return e.join(" ")}var o={}.hasOwnProperty;"undefined"!=typeof e&&e.exports?e.exports=i:(n=[],s=function(){return i}.apply(t,n),!(void 0!==s&&(e.exports=s)))}()},function(e,t,i){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function s(e){if(Array.isArray(e)){for(var t=0,i=Array(e.length);t<e.length;t++)i[t]=e[t];return i}return Array.from(e)}function o(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var r=function(){function e(e,t){for(var i=0;i<t.length;i++){var n=t[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,i,n){return i&&e(t.prototype,i),n&&e(t,n),t}}(),a=i(5),c=i(26),l=n(c),h=function(){function e(){o(this,e),this.store=(0,a.createStore)(l.default,window.devToolsExtension?window.devToolsExtension():void 0)}return r(e,[{key:"getState",value:function(){return this.store.getState()}},{key:"dispatch",value:function(e){this.store.dispatch(e)}},{key:"subscribe",value:function(e){this.store.subscribe(e)}},{key:"getItems",value:function(){var e=this.store.getState();return e.items}},{key:"getItemsFilteredByActive",value:function(){var e=this.getItems(),t=e.filter(function(e){return e.active===!0},[]);return t}},{key:"getItemsReducedToValues",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:this.getItems(),t=e.reduce(function(e,t){return e.push(t.value),e},[]);return t}},{key:"getChoices",value:function(){var e=this.store.getState();return e.choices}},{key:"getChoicesFilteredByActive",value:function(){var e=this.getChoices(),t=e.filter(function(e){return e.active===!0});return t}},{key:"getChoicesFilteredBySelectable",value:function(){var e=this.getChoices(),t=e.filter(function(e){return e.disabled!==!0});return t}},{key:"getSearchableChoices",value:function(){var e=this.getChoicesFilteredBySelectable();return e.filter(function(e){return e.placeholder!==!0})}},{key:"getChoiceById",value:function(e){if(e){var t=this.getChoicesFilteredByActive(),i=t.find(function(t){return t.id===parseInt(e,10)});return i}return!1}},{key:"getGroups",value:function(){var e=this.store.getState();return e.groups}},{key:"getGroupsFilteredByActive",value:function(){var e=this.getGroups(),t=this.getChoices(),i=e.filter(function(e){var i=e.active===!0&&e.disabled===!1,n=t.some(function(e){return e.active===!0&&e.disabled===!1});return i&&n},[]);return i}},{key:"getGroupById",value:function(e){var t=this.getGroups(),i=t.find(function(t){return t.id===e});return i}},{key:"getPlaceholderChoice",value:function(){var e=this.getChoices(),t=[].concat(s(e)).reverse().find(function(e){return e.placeholder===!0});return t}}]),e}();t.default=h,e.exports=h},function(e,t,i){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}t.__esModule=!0,t.compose=t.applyMiddleware=t.bindActionCreators=t.combineReducers=t.createStore=void 0;var s=i(6),o=n(s),r=i(21),a=n(r),c=i(23),l=n(c),h=i(24),u=n(h),d=i(25),f=n(d),p=i(22);n(p);t.createStore=o.default,t.combineReducers=a.default,t.bindActionCreators=l.default,t.applyMiddleware=u.default,t.compose=f.default},function(e,t,i){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function s(e,t,i){function n(){g===m&&(g=m.slice())}function o(){return v}function a(e){if("function"!=typeof e)throw new Error("Expected listener to be a function.");var t=!0;return n(),g.push(e),function(){if(t){t=!1,n();var i=g.indexOf(e);g.splice(i,1)}}}function h(e){if(!(0,r.default)(e))throw new Error("Actions must be plain objects. Use custom middleware for async actions.");if("undefined"==typeof e.type)throw new Error('Actions may not have an undefined "type" property. Have you misspelled a constant?');if(y)throw new Error("Reducers may not dispatch actions.");try{y=!0,v=p(v,e)}finally{y=!1}for(var t=m=g,i=0;i<t.length;i++)t[i]();return e}function u(e){if("function"!=typeof e)throw new Error("Expected the nextReducer to be a function.");p=e,h({type:l.INIT})}function d(){var e,t=a;return e={subscribe:function(e){function i(){e.next&&e.next(o())}if("object"!=typeof e)throw new TypeError("Expected the observer to be an object.");i();var n=t(i);return{unsubscribe:n}}},e[c.default]=function(){return this},e}var f;if("function"==typeof t&&"undefined"==typeof i&&(i=t,t=void 0),"undefined"!=typeof i){if("function"!=typeof i)throw new Error("Expected the enhancer to be a function.");return i(s)(e,t)}if("function"!=typeof e)throw new Error("Expected the reducer to be a function.");var p=e,v=t,m=[],g=m,y=!1;return h({type:l.INIT}),f={dispatch:h,subscribe:a,getState:o,replaceReducer:u},f[c.default]=d,f}t.__esModule=!0,t.ActionTypes=void 0,t.default=s;var o=i(7),r=n(o),a=i(17),c=n(a),l=t.ActionTypes={INIT:"@@redux/INIT"}},function(e,t,i){function n(e){if(!r(e)||s(e)!=a)return!1;var t=o(e);if(null===t)return!0;var i=u.call(t,"constructor")&&t.constructor;return"function"==typeof i&&i instanceof i&&h.call(i)==d}var s=i(8),o=i(14),r=i(16),a="[object Object]",c=Function.prototype,l=Object.prototype,h=c.toString,u=l.hasOwnProperty,d=h.call(Object);e.exports=n},function(e,t,i){function n(e){return null==e?void 0===e?c:a:l&&l in Object(e)?o(e):r(e)}var s=i(9),o=i(12),r=i(13),a="[object Null]",c="[object Undefined]",l=s?s.toStringTag:void 0;e.exports=n},function(e,t,i){var n=i(10),s=n.Symbol;e.exports=s},function(e,t,i){var n=i(11),s="object"==typeof self&&self&&self.Object===Object&&self,o=n||s||Function("return this")();e.exports=o},function(e,t){(function(t){var i="object"==typeof t&&t&&t.Object===Object&&t;e.exports=i}).call(t,function(){return this}())},function(e,t,i){function n(e){var t=r.call(e,c),i=e[c];try{e[c]=void 0;var n=!0}catch(e){}var s=a.call(e);return n&&(t?e[c]=i:delete e[c]),s}var s=i(9),o=Object.prototype,r=o.hasOwnProperty,a=o.toString,c=s?s.toStringTag:void 0;e.exports=n},function(e,t){function i(e){return s.call(e)}var n=Object.prototype,s=n.toString;e.exports=i},function(e,t,i){var n=i(15),s=n(Object.getPrototypeOf,Object);e.exports=s},function(e,t){function i(e,t){return function(i){return e(t(i))}}e.exports=i},function(e,t){function i(e){return null!=e&&"object"==typeof e}e.exports=i},function(e,t,i){e.exports=i(18)},function(e,t,i){(function(e,n){"use strict";function s(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var o,r=i(20),a=s(r);o="undefined"!=typeof self?self:"undefined"!=typeof window?window:"undefined"!=typeof e?e:n;var c=(0,a.default)(o);t.default=c}).call(t,function(){return this}(),i(19)(e))},function(e,t){e.exports=function(e){return e.webpackPolyfill||(e.deprecate=function(){},e.paths=[],e.children=[],e.webpackPolyfill=1),e}},function(e,t){"use strict";function i(e){var t,i=e.Symbol;return"function"==typeof i?i.observable?t=i.observable:(t=i("observable"),i.observable=t):t="@@observable",t}Object.defineProperty(t,"__esModule",{value:!0}),t.default=i},function(e,t,i){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function s(e,t){var i=t&&t.type,n=i&&'"'+i.toString()+'"'||"an action";return"Given action "+n+', reducer "'+e+'" returned undefined. To ignore an action, you must explicitly return the previous state.'}function o(e){Object.keys(e).forEach(function(t){var i=e[t],n=i(void 0,{type:a.ActionTypes.INIT});if("undefined"==typeof n)throw new Error('Reducer "'+t+'" returned undefined during initialization. If the state passed to the reducer is undefined, you must explicitly return the initial state. The initial state may not be undefined.');var s="@@redux/PROBE_UNKNOWN_ACTION_"+Math.random().toString(36).substring(7).split("").join(".");if("undefined"==typeof i(void 0,{type:s}))throw new Error('Reducer "'+t+'" returned undefined when probed with a random type. '+("Don't try to handle "+a.ActionTypes.INIT+' or other actions in "redux/*" ')+"namespace. They are considered private. Instead, you must return the current state for any unknown actions, unless it is undefined, in which case you must return the initial state, regardless of the action type. The initial state may not be undefined.")})}function r(e){for(var t=Object.keys(e),i={},n=0;n<t.length;n++){var r=t[n];"function"==typeof e[r]&&(i[r]=e[r])}var a,c=Object.keys(i);try{o(i)}catch(e){a=e}return function(){var e=arguments.length<=0||void 0===arguments[0]?{}:arguments[0],t=arguments[1];if(a)throw a;for(var n=!1,o={},r=0;r<c.length;r++){var l=c[r],h=i[l],u=e[l],d=h(u,t);if("undefined"==typeof d){var f=s(l,t);throw new Error(f)}o[l]=d,n=n||d!==u}return n?o:e}}t.__esModule=!0,t.default=r;var a=i(6),c=i(7),l=(n(c),i(22));n(l)},function(e,t){"use strict";function i(e){"undefined"!=typeof console&&"function"==typeof console.error&&console.error(e);try{throw new Error(e)}catch(e){}}t.__esModule=!0,t.default=i},function(e,t){"use strict";function i(e,t){return function(){return t(e.apply(void 0,arguments))}}function n(e,t){if("function"==typeof e)return i(e,t);if("object"!=typeof e||null===e)throw new Error("bindActionCreators expected an object or a function, instead received "+(null===e?"null":typeof e)+'. Did you write "import ActionCreators from" instead of "import * as ActionCreators from"?');for(var n=Object.keys(e),s={},o=0;o<n.length;o++){var r=n[o],a=e[r];"function"==typeof a&&(s[r]=i(a,t))}return s}t.__esModule=!0,t.default=n},function(e,t,i){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function s(){for(var e=arguments.length,t=Array(e),i=0;i<e;i++)t[i]=arguments[i];return function(e){return function(i,n,s){var r=e(i,n,s),c=r.dispatch,l=[],h={getState:r.getState,dispatch:function(e){return c(e)}};return l=t.map(function(e){return e(h)}),c=a.default.apply(void 0,l)(r.dispatch),o({},r,{dispatch:c})}}}t.__esModule=!0;var o=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var i=arguments[t];for(var n in i)Object.prototype.hasOwnProperty.call(i,n)&&(e[n]=i[n])}return e};t.default=s;var r=i(25),a=n(r)},function(e,t){"use strict";function i(){for(var e=arguments.length,t=Array(e),i=0;i<e;i++)t[i]=arguments[i];if(0===t.length)return function(e){return e};if(1===t.length)return t[0];var n=t[t.length-1],s=t.slice(0,-1);return function(){return s.reduceRight(function(e,t){return t(e)},n.apply(void 0,arguments))}}t.__esModule=!0,t.default=i},function(e,t,i){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var s=i(5),o=i(27),r=n(o),a=i(28),c=n(a),l=i(29),h=n(l),u=(0,s.combineReducers)({items:r.default,groups:c.default,choices:h.default}),d=function(e,t){var i=e;return"CLEAR_ALL"===t.type&&(i=void 0),u(i,t)};t.default=d},function(e,t){"use strict";function i(e){if(Array.isArray(e)){for(var t=0,i=Array(e.length);t<e.length;t++)i[t]=e[t];return i}return Array.from(e)}Object.defineProperty(t,"__esModule",{value:!0});var n=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],t=arguments[1];switch(t.type){case"ADD_ITEM":var n=[].concat(i(e),[{id:t.id,choiceId:t.choiceId,groupId:t.groupId,value:t.value,label:t.label,active:!0,highlighted:!1,customProperties:t.customProperties,placeholder:t.placeholder||!1,keyCode:null}]);return n.map(function(e){return e.highlighted&&(e.highlighted=!1),e});case"REMOVE_ITEM":return e.map(function(e){return e.id===t.id&&(e.active=!1),e});case"HIGHLIGHT_ITEM":return e.map(function(e){return e.id===t.id&&(e.highlighted=t.highlighted),e});default:return e}};t.default=n},function(e,t){"use strict";function i(e){if(Array.isArray(e)){for(var t=0,i=Array(e.length);t<e.length;t++)i[t]=e[t];return i}return Array.from(e)}Object.defineProperty(t,"__esModule",{value:!0});var n=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],t=arguments[1];switch(t.type){case"ADD_GROUP":return[].concat(i(e),[{id:t.id,value:t.value,active:t.active,disabled:t.disabled}]);case"CLEAR_CHOICES":return e.groups=[];default:return e}};t.default=n},function(e,t){"use strict";function i(e){if(Array.isArray(e)){for(var t=0,i=Array(e.length);t<e.length;t++)i[t]=e[t];return i}return Array.from(e)}Object.defineProperty(t,"__esModule",{value:!0});var n=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],t=arguments[1];switch(t.type){case"ADD_CHOICE":return[].concat(i(e),[{id:t.id,elementId:t.elementId,groupId:t.groupId,value:t.value,label:t.label||t.value,disabled:t.disabled||!1,selected:!1,active:!0,score:9999,customProperties:t.customProperties,placeholder:t.placeholder||!1,keyCode:null}]);case"ADD_ITEM":var n=e;return t.activateOptions&&(n=e.map(function(e){return e.active=t.active,e})),t.choiceId>-1&&(n=e.map(function(e){return e.id===parseInt(t.choiceId,10)&&(e.selected=!0),e})),n;case"REMOVE_ITEM":return t.choiceId>-1?e.map(function(e){return e.id===parseInt(t.choiceId,10)&&(e.selected=!1),e}):e;case"FILTER_CHOICES":var s=t.results,o=e.map(function(e){return e.active=s.some(function(t){return t.item.id===e.id&&(e.score=t.score,!0)}),e});return o;case"ACTIVATE_CHOICES":return e.map(function(e){return e.active=t.active,e});case"CLEAR_CHOICES":return e.choices=[];default:return e}};t.default=n},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0});t.addItem=function(e,t,i,n,s,o,r,a){return{type:"ADD_ITEM",value:e,label:t,id:i,choiceId:n,groupId:s,customProperties:o,placeholder:r,keyCode:a}},t.removeItem=function(e,t){return{type:"REMOVE_ITEM",id:e,choiceId:t}},t.highlightItem=function(e,t){return{type:"HIGHLIGHT_ITEM",id:e,highlighted:t}},t.addChoice=function(e,t,i,n,s,o,r,a,c){return{type:"ADD_CHOICE",value:e,label:t,id:i,groupId:n,disabled:s,elementId:o,customProperties:r,placeholder:a,keyCode:c}},t.filterChoices=function(e){return{type:"FILTER_CHOICES",results:e}},t.activateChoices=function(){var e=!(arguments.length>0&&void 0!==arguments[0])||arguments[0];return{type:"ACTIVATE_CHOICES",active:e}},t.clearChoices=function(){return{type:"CLEAR_CHOICES"}},t.addGroup=function(e,t,i,n){return{type:"ADD_GROUP",value:e,id:t,active:i,disabled:n}},t.clearAll=function(){return{type:"CLEAR_ALL"}}},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},n=(t.capitalise=function(e){return e.replace(/\w\S*/g,function(e){return e.charAt(0).toUpperCase()+e.substr(1).toLowerCase()})},t.generateChars=function(e){for(var t="",i=0;i<e;i++){var n=a(0,36);t+=n.toString(36)}return t}),s=(t.generateId=function(e,t){var i=e.id||e.name&&e.name+"-"+n(2)||n(4);return i=i.replace(/(:|\.|\[|\]|,)/g,""),i=t+i},t.getType=function(e){return Object.prototype.toString.call(e).slice(8,-1)}),o=t.isType=function(e,t){var i=s(t);return void 0!==t&&null!==t&&i===e},r=(t.isNode=function(e){return"object"===("undefined"==typeof Node?"undefined":i(Node))?e instanceof Node:e&&"object"===("undefined"==typeof e?"undefined":i(e))&&"number"==typeof e.nodeType&&"string"==typeof e.nodeName},t.isElement=function(e){return"object"===("undefined"==typeof HTMLElement?"undefined":i(HTMLElement))?e instanceof HTMLElement:e&&"object"===("undefined"==typeof e?"undefined":i(e))&&null!==e&&1===e.nodeType&&"string"==typeof e.nodeName},t.extend=function e(){for(var t={},i=arguments.length,n=function(i){for(var n in i)Object.prototype.hasOwnProperty.call(i,n)&&(o("Object",i[n])?t[n]=e(!0,t[n],i[n]):t[n]=i[n])},s=0;s<i;s++){var r=arguments[s];o("Object",r)&&n(r)}return t},t.whichTransitionEvent=function(){var e,t=document.createElement("fakeelement"),i={transition:"transitionend",
OTransition:"oTransitionEnd",MozTransition:"transitionend",WebkitTransition:"webkitTransitionEnd"};for(e in i)if(void 0!==t.style[e])return i[e]},t.whichAnimationEvent=function(){var e,t=document.createElement("fakeelement"),i={animation:"animationend",OAnimation:"oAnimationEnd",MozAnimation:"animationend",WebkitAnimation:"webkitAnimationEnd"};for(e in i)if(void 0!==t.style[e])return i[e]}),a=(t.getParentsUntil=function(e,t,i){for(var n=[];e&&e!==document;e=e.parentNode){if(t){var s=t.charAt(0);if("."===s&&e.classList.contains(t.substr(1)))break;if("#"===s&&e.id===t.substr(1))break;if("["===s&&e.hasAttribute(t.substr(1,t.length-1)))break;if(e.tagName.toLowerCase()===t)break}if(i){var o=i.charAt(0);"."===o&&e.classList.contains(i.substr(1))&&n.push(e),"#"===o&&e.id===i.substr(1)&&n.push(e),"["===o&&e.hasAttribute(i.substr(1,i.length-1))&&n.push(e),e.tagName.toLowerCase()===i&&n.push(e)}else n.push(e)}return 0===n.length?null:n},t.wrap=function(e,t){return t=t||document.createElement("div"),e.nextSibling?e.parentNode.insertBefore(t,e.nextSibling):e.parentNode.appendChild(t),t.appendChild(e)},t.getSiblings=function(e){for(var t=[],i=e.parentNode.firstChild;i;i=i.nextSibling)1===i.nodeType&&i!==e&&t.push(i);return t},t.findAncestor=function(e,t){for(;(e=e.parentElement)&&!e.classList.contains(t););return e},t.findAncestorByAttrName=function(e,t){for(var i=e;i;){if(i.hasAttribute(t))return i;i=i.parentElement}return null},t.debounce=function(e,t,i){var n;return function(){var s=this,o=arguments,r=function(){n=null,i||e.apply(s,o)},a=i&&!n;clearTimeout(n),n=setTimeout(r,t),a&&e.apply(s,o)}},t.getElemDistance=function(e){var t=0;if(e.offsetParent)do t+=e.offsetTop,e=e.offsetParent;while(e);return t>=0?t:0},t.getElementOffset=function(e,t){var i=t;return i>1&&(i=1),i>0&&(i=0),Math.max(e.offsetHeight*i)},t.getAdjacentEl=function(e,t){var i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:1;if(e&&t){var n=e.parentNode.parentNode,s=Array.from(n.querySelectorAll(t)),o=s.indexOf(e),r=i>0?1:-1;return s[o+r]}},t.getScrollPosition=function(e){return"bottom"===e?Math.max((window.scrollY||window.pageYOffset)+(window.innerHeight||document.documentElement.clientHeight)):window.scrollY||window.pageYOffset},t.isInView=function(e,t,i){return this.getScrollPosition(t)>this.getElemDistance(e)+this.getElementOffset(e,i)},t.isScrolledIntoView=function(e,t){var i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:1;if(e){var n=void 0;return n=i>0?t.scrollTop+t.offsetHeight>=e.offsetTop+e.offsetHeight:e.offsetTop>=t.scrollTop}},t.stripHTML=function(e){var t=document.createElement("DIV");return t.innerHTML=e,t.textContent||t.innerText||""},t.addAnimation=function(e,t){var i=r(),n=function n(){e.classList.remove(t),e.removeEventListener(i,n,!1)};e.classList.add(t),e.addEventListener(i,n,!1)},t.getRandomNumber=function(e,t){return Math.floor(Math.random()*(t-e)+e)}),c=t.strToEl=function(){var e=document.createElement("div");return function(t){var i=t.trim(),n=void 0;for(e.innerHTML=i,n=e.children[0];e.firstChild;)e.removeChild(e.firstChild);return n}}();t.getWidthOfInput=function(e){var t=e.value||e.placeholder,i=e.offsetWidth;if(t){var n=c("<span>"+t+"</span>");if(n.style.position="absolute",n.style.padding="0",n.style.top="-9999px",n.style.left="-9999px",n.style.width="auto",n.style.whiteSpace="pre",document.body.contains(e)&&window.getComputedStyle){var s=window.getComputedStyle(e);s&&(n.style.fontSize=s.fontSize,n.style.fontFamily=s.fontFamily,n.style.fontWeight=s.fontWeight,n.style.fontStyle=s.fontStyle,n.style.letterSpacing=s.letterSpacing,n.style.textTransform=s.textTransform,n.style.padding=s.padding)}document.body.appendChild(n),t&&n.offsetWidth!==e.offsetWidth&&(i=n.offsetWidth+4),document.body.removeChild(n)}return i+"px"},t.sortByAlpha=function(e,t){var i=(e.label||e.value).toLowerCase(),n=(t.label||t.value).toLowerCase();return i<n?-1:i>n?1:0},t.sortByScore=function(e,t){return e.score-t.score},t.triggerEvent=function(e,t){var i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,n=new CustomEvent(t,{detail:i,bubbles:!0,cancelable:!0});return e.dispatchEvent(n)}},function(e,t){"use strict";!function(){function e(e,t){t=t||{bubbles:!1,cancelable:!1,detail:void 0};var i=document.createEvent("CustomEvent");return i.initCustomEvent(e,t.bubbles,t.cancelable,t.detail),i}Array.from||(Array.from=function(){var e=Object.prototype.toString,t=function(t){return"function"==typeof t||"[object Function]"===e.call(t)},i=function(e){var t=Number(e);return isNaN(t)?0:0!==t&&isFinite(t)?(t>0?1:-1)*Math.floor(Math.abs(t)):t},n=Math.pow(2,53)-1,s=function(e){var t=i(e);return Math.min(Math.max(t,0),n)};return function(e){var i=this,n=Object(e);if(null==e)throw new TypeError("Array.from requires an array-like object - not null or undefined");var o,r=arguments.length>1?arguments[1]:void 0;if("undefined"!=typeof r){if(!t(r))throw new TypeError("Array.from: when provided, the second argument must be a function");arguments.length>2&&(o=arguments[2])}for(var a,c=s(n.length),l=t(i)?Object(new i(c)):new Array(c),h=0;h<c;)a=n[h],r?l[h]="undefined"==typeof o?r(a,h):r.call(o,a,h):l[h]=a,h+=1;return l.length=c,l}}()),Array.prototype.find||(Array.prototype.find=function(e){if(null==this)throw new TypeError("Array.prototype.find called on null or undefined");if("function"!=typeof e)throw new TypeError("predicate must be a function");for(var t,i=Object(this),n=i.length>>>0,s=arguments[1],o=0;o<n;o++)if(t=i[o],e.call(s,t,o,i))return t}),e.prototype=window.Event.prototype,window.CustomEvent=e}()}])});
//# sourceMappingURL=choices.min.js.map
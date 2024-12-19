_Sprite_Destination_createBitmap = Sprite_Destination.prototype.createBitmap;
Sprite_Destination.prototype.createBitmap = function() {
    _Sprite_Destination_createBitmap.call(this);
    this.bitmap.fillAll('black');
};
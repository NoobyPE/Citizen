<?php

namespace nooby\CitizenLibrary\attributes;

use pocketmine\entity\Skin;

final class CitizenSkin
{
	public static function getSkinDataFromPNG(string $path): string
	{
		$bytes = "";
		if (!file_exists($path)) {
			return $bytes;
		}
		$img = imagecreatefrompng($path);
		[$width, $height] = getimagesize($path);
		for ($y = 0; $y < $height; ++$y) {
			for ($x = 0; $x < $width; ++$x) {
				$argb = imagecolorat($img, $x, $y);
				$bytes .= chr(($argb >> 16) & 0xff) . chr(($argb >> 8) & 0xff) . chr($argb & 0xff) . chr((~($argb >> 24) << 1) & 0xff);
			}
		}
		imagedestroy($img);
		return $bytes;
	}
	
	public static function fromDefaultGeometry(string $skinPath): Skin
	{
		return new Skin("Standard_CustomSlim", self::getSkinDataFromPNG($skinPath), "", "geometry.humanoid.custom", "");
	}

	/**
        * USE: fromCustomGeometry("skywars, PluginBase::getDataFolder() . "SkyWars.png", PluginBase::getDataFolder() . "SkyWars.geo.json");
	**/
	public static function fromCustomGeometry(string $geometry, string $skinPath, string $skinGeometryPath, string $capePath = ''): Skin
	{
		$img = imagecreatefrompng($skinPath);
		$skin_bytes = self::getSkinDataFromPNG($skinPath);
		//TODO: how to use skinId????
		return new Skin($c['skinId'], $skin_bytes, $capePath, 'geometry' . $geometry, file_get_contents($skinGeometryPath));
	}
}

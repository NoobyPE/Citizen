<?php

namespace nooby\CitizenLibrary\attributes;

use GdImage;
use nooby\citizen\CitizenLibrary;
use pocketmine\entity\Skin;
use pocketmine\player\Player;

final class CitizenSkin
{
	const ACCEPTED_SKIN_SIZE = [
        64 * 32 * 4,
        64 * 64 * 4,
        128 * 128 * 4
    ];

    const SKIN_WIDTH_MAP = [
        64 * 32 * 4 => 64,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128
    ];

    const SKIN_HEIGHT_MAP = [
        64 * 32 * 4 => 32,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128
    ];

	static function validateSize(int $size): bool
	{
		return in_array($size, self::ACCEPTED_SKIN_SIZE, true);
	}

	static function getSkinDataFromPNG(string $path): string
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
	
	static function fromDefaultGeometry(string $skinPath = '', string $capePath = ''): Skin
	{
		return new Skin("Standard_CustomSlim", self::getSkinDataFromPNG($skinPath), self::getSkinDataFromPNG($capePath), "geometry.humanoid.custom", "");
	}

	/**
        * USE: fromCustomGeometry($player->getSkin()->getSkinId(), "skywars, PluginBase::getDataFolder() . "SkyWars.png", PluginBase::getDataFolder() . "SkyWars.geo.json");
	**/
	static function fromCustomGeometry(string $id, string $geometry, string $skinPath, string $skinGeometryPath, string $capePath = ''): Skin
	{
		$img = imagecreatefrompng($skinPath);
		$skin_bytes = self::getSkinDataFromPNG($skinPath);
		//TODO: how to use skinId????
		return new Skin($id, $skin_bytes, self::getSkinDataFromPNG($capePath), 'geometry' . $geometry, file_get_contents($skinGeometryPath));
	}

	static function saveSkin(string $name, string $skinData): bool
	{
		$folder = CitizenLibrary::getInstance()->getPlugin()->getDataFolder();
		if (file_exists($folder . "skins" . DIRECTORY_SEPARATOR . $name . ".png")) {
			return true;
		}
		$img = self::getSkinDataToImage($skinData);
		if (is_null($img)) {
			return false;
		}
		imagepng($img, $folder . "skins" . DIRECTORY_SEPARATOR . $name . ".png");
	}

	static function getSkinDataToImage($skinData): ?GdImage
    {
        $size = strlen($skinData);
        if (!self::validateSize($size)) {
            return null;
        }
        $width = self::SKIN_WIDTH_MAP[$size];
        $height = self::SKIN_HEIGHT_MAP[$size];
        $skinPos = 0;
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            return null;
        }

        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $r = ord($skinData[$skinPos]);
                $skinPos++;
                $g = ord($skinData[$skinPos]);
                $skinPos++;
                $b = ord($skinData[$skinPos]);
                $skinPos++;
                $a = 127 - intdiv(ord($skinData[$skinPos]), 2);
                $skinPos++;
                $col = imagecolorallocatealpha($image, $r, $g, $b, $a);
                imagesetpixel($image, $x, $y, $col);
            }
        }
        imagesavealpha($image, true);
        return $image;
    }

	static function addOtherGeometry(Player $player, string $geometry, string $pngFile, string $geometryPath): void
	{
        $skin = $player->getSkin();
        $size = getimagesize($geometryPath);

        $path = self::setTempImage($geometryPath, $pngFile, 'geometry' . $geometry, [$size[0], $size[1], 4]);
        $img = @imagecreatefrompng($geometryPath);
        $bytes = "";
        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $x < $size[0]; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~(($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        $cape = is_null($skin->getCapeData()) ? '' : $skin->getCapeData();
        $player->setSkin(new Skin($skin->getSkinId(), $bytes, $cape, $geometry, file_get_contents($geometryPath)));
        $player->sendSkin();
    }

    static function setTempImage(string $skinPath, string $dataFolder, string $name, array $size): string
    {
        $down = imagecreatefrompng($skinPath);

		$folder = CitizenLibrary::getInstance()->getPlugin()->getDataFolder();
        if ($size[0] * $size[1] * $size[2] == 65536) {
            $upper = self::setMakeover($folder . $name . ".png", 128, 128);
        } else {
            $upper = self::setMakeover($folder . $name . ".png", 64, 64);
        }

        imagealphablending($down, true);
        imagesavealpha($down, true);
        imagecopymerge($down, $upper, 0, 0, 0, 0, $size[0], $size[1], 100);
        imagepng($down, $folder . 'temp.png');
        return $folder . 'temp.png';
    }

	static function setMakeover(string $filename, int $w, int $h, bool $crop = false): ?GdImage
	{
        list($width, $height) = getimagesize($filename);
        $r = $width / $height;
        if($crop){
            if($width > $height){
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $new_width = $w;
            $new_height = $h;
        } else {
            if($w / $h > $r){
                $new_width = $h * $r;
                $new_height = $h;
            } else {
                $new_height = $w / $r;
                $new_width = $w;
            }
        }
        $src = imagecreatefrompng($filename);
        $dst = imagecreatetruecolor($w, $h);
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        return $dst;
    }

}
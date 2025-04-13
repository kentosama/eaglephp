<?php

namespace Eagle;

use InvalidArgumentException;
use Exception;

class Image
{
    /**
     * Récupérer le type mime d'une image.
     * 
     * @param string $src Chemin du fichier
     * @return string
     * @throws InvalidArgumentException Si le fichier n'est pas une image
     */
    public static function mimeType(string $src): string
    {
        if (!file_exists($src)) {
            throw new InvalidArgumentException("Le fichier '$src' n'existe pas.");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $src);
        finfo_close($finfo);

        if (!in_array($mime, ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'])) {
            throw new InvalidArgumentException("Le fichier '$src' n'est pas une image valide.");
        }

        return $mime;
    }

    /**
     * Recadrer une image et l'enregistrer sur le serveur.
     *
     * @param string $src Chemin vers le fichier source
     * @param string $dst Chemin vers le fichier de destination
     * @param array $size Dimensions du recadrage [width, height]
     * @return bool
     * @throws Exception Si l'image ne peut pas être traitée
     */
    public static function crop(string $src, string $dst, array $size = [250, 250]): bool
    {
        list($width, $height) = getimagesize($src);
        $mime = self::mimeType($src);
        
        // Déterminer le type d'image
        switch ($mime) {
            case 'image/jpg':
            case 'image/jpeg':
                $img = imagecreatefromjpeg($src);
                $extension = '.jpg';
                break;

            case 'image/png':
                $img = imagecreatefrompng($src);
                $extension = '.png';
                break;

            case 'image/webp':
                $img = imagecreatefromwebp($src);
                $extension = '.webp';
                break;

            default:
                throw new Exception("Format de l'image non supporté: $mime");
        }

        // Calcul des coordonnées de recadrage
        if ($width > $height) {
            $x = ($width - $height) / 2;
            $y = 0;
            $smallestSide = $height;
        } else {
            $x = 0;
            $y = ($height - $width) / 2;
            $smallestSide = $width;
        }

        $crop = imagecreatetruecolor($size[0], $size[1]);
        imagecopyresampled($crop, $img, 0, 0, $x, $y, $size[0], $size[1], $smallestSide, $smallestSide);
        
        // Sauvegarde de l'image recadrée
        switch ($extension) {
            case '.jpg':
                imagejpeg($crop, $dst);
                break;

            case '.png':
                imagepng($crop, $dst);
                break;

            case '.webp':
                imagewebp($crop, $dst);
                break;
        }

        imagedestroy($img);
        imagedestroy($crop);

        return file_exists($dst);
    }
}

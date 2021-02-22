<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;

class Image
{

    /**
     * @brief Récupérer le type mime d'une image.
     * @param $src Chaîne contenant le nom du fichier.
     * @return string
     */
    static public function mimeType(string $src): string
    {
        $mime = '';
        
        if(file_exists($src))
        {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $src);
            finfo_close($finfo);
        }

        return $mime;
    }

    /**
     * @brief Recadrer une image JPG ou PNG et l'enregistrer sur le serveur.
     * @param $src Chemin vers le fichier source.
     * @param $dst Chemin vers le fichier de destination.
     * @param $size Tableau contenant la nouvelle dimention de l'image [$width, $height].
     * @return void
     */
    static public function crop(string $src, string $dst, array $size = [250, 250]): bool
    {
        
        list($width, $height) = getimagesize($src);

        $mime = Image::mimeType($src);

       
        if(in_array($mime, ['image/jpg', 'image/jpeg']))
        {
            $img = imagecreatefromjpeg($src);
            $extension = '.jpg';
        }
        else if($mime  === 'image/png')
        {
            $img = imagecreatefrompng($src);
            $extension = '.png';
        } 
        
        if ($width > $height) 
        {
            $y = 0;
            $x = ($width - $height) / 2;
            $smallestSide = $height;
        } 
        else 
        {
            $x = 0;
            $y = ($height - $width) / 2;
            $smallestSide = $width;
        }

        $crop = imagecreatetruecolor($size[0], $size[1]);
        imagecopyresampled($crop, $img, 0, 0, $x, $y, $size[0], $size[1], $smallestSide, $smallestSide);
        
        if($extension === '.jpg')
            imagejpeg($crop, $dst);
        else if($extension === '.png')
            imagepng($crop, $dst);

        imagedestroy($img);
        imagedestroy($crop);

        return file_exists($dst);
    }
}
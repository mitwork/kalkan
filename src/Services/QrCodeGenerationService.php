<?php

namespace Mitwork\Kalkan\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Mitwork\Kalkan\Contracts\BaseService;

class QrCodeGenerationService extends BaseService
{
    protected PngWriter $writer;

    protected string $link;

    /**
     * Генерация QR-кода
     *
     * @param  string  $link Ссылка
     * @param  int  $size Размер
     * @param  int  $margin Отступы
     * @param  string|null  $prefix Префикс ссылки
     * @return ResultInterface Результаты генерации
     */
    public function generate(string $link, int $size = 200, int $margin = 5, ?string $prefix = ''): ResultInterface
    {
        if ($prefix === '') {
            $prefix = config('kalkan.links.prefix', $prefix);
        }

        if ($prefix) {
            $link = sprintf('%s%s', $prefix, $link);
        }

        $qrCode = QrCode::create($link)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize($size)
            ->setMargin($margin)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $this->link = $link;
        $this->writer = new PngWriter();

        return $this->writer->write($qrCode);
    }

    /**
     * Проверка QR-кода
     *
     * @param  string  $link Ожидаемая ссылка
     * @return bool Результат
     */
    public function validate(string $link, string $prefix = ''): bool
    {
        if (! $prefix) {
            $prefix = config('kalkan.links.prefix', $prefix);
        }

        if ($prefix) {
            $link = sprintf('%s%s', $prefix, $link);
        }

        try {
            $this->writer->validateResult($this->generate($this->link, prefix: null), $link);
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());

            return false;
        }

        return true;

    }
}

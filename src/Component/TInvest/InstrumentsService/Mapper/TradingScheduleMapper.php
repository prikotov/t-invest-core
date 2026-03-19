<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use DateTimeImmutable;
use DateTimeInterface;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\TradingDayDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\TradingScheduleDto;

final class TradingScheduleMapper
{
    public function map(string $json): TradingScheduleDto
    {
        $data = json_decode($json, true);
        $exchanges = [];

        foreach ($data['exchanges'] ?? [] as $exchange) {
            $days = [];
            foreach ($exchange['days'] ?? [] as $day) {
                $days[] = new TradingDayDto(
                    exchange: $exchange['exchange'] ?? '',
                    date: $this->parseDate($day['date'] ?? ''),
                    isTradingDay: $day['isTradingDay'] ?? false,
                    startTime: $this->parseTime($day['startTime'] ?? null),
                    endTime: $this->parseTime($day['endTime'] ?? null),
                    morningSessionStart: $this->parseTime($day['morningTradingStartTime'] ?? null),
                    morningSessionEnd: $this->parseTime($day['morningTradingEndTime'] ?? null),
                    eveningSessionStart: $this->parseTime($day['eveningTradingStartTime'] ?? null),
                    eveningSessionEnd: $this->parseTime($day['eveningTradingEndTime'] ?? null),
                    clearingStart: $this->parseTime($day['clearingStartTime'] ?? null),
                    clearingEnd: $this->parseTime($day['clearingEndTime'] ?? null),
                    holidayName: $day['holidayName'] ?? null,
                );
            }
            $exchanges[] = new TradingScheduleDto(
                exchange: $exchange['exchange'] ?? '',
                days: $days,
            );
        }

        return $exchanges[0] ?? new TradingScheduleDto('', []);
    }

    private function parseDate(string $date): DateTimeInterface
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $date);
        if ($dt === false) {
            $dt = DateTimeImmutable::createFromFormat('Y-m-d', substr($date, 0, 10));
        }
        return $dt ?: new DateTimeImmutable();
    }

    private function parseTime(?string $time): ?DateTimeInterface
    {
        if ($time === null || $time === '') {
            return null;
        }
        $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $time);
        if ($dt === false) {
            $dt = DateTimeImmutable::createFromFormat('H:i:s', $time);
        }
        return $dt ?: null;
    }
}

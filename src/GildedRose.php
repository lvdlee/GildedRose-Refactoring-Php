<?php

declare(strict_types=1);

namespace GildedRose;

class ItemName
{
    public const AGED_BRIE = 'Aged Brie';
    public const BACKSTAGE_PASSES = 'Backstage passes to a TAFKAL80ETC concert';
    public const SULFURAS = 'Sulfuras, Hand of Ragnaros';
    public const CONJURED = 'Conjured Mana Cake';
}

final class GildedRose
{
    /**
     * @param Item[] $items
     */
    public function __construct(
        private array $items
    ) {
    }

    // ItemName::SULFURAS, being a legendary item, never has to be sold or decreases in Quality
    private const doesNotChange = [
        ItemName::SULFURAS
    ];

    private const increasesInQuality = [
        ItemName::AGED_BRIE,
        ItemName::BACKSTAGE_PASSES
    ];

    private const minQuality = 0;

    // The max quality does not apply to ItemName::SULFURAS
    private const maxQuality = 50;

    public function updateQuality(): void
    {
        foreach ($this->items as $item) {
            // Items that do not change can be skipped
            if (in_array($item->name, GildedRose::doesNotChange, true)) {
                continue;
            }

            if (in_array($item->name, GildedRose::increasesInQuality, true)) {
                $item->quality = $this->getIncreasedQuality($item);
            }

            $item->sellIn = $item->sellIn - 1;

            if ($item->sellIn < 0) {
                $item->quality = $this->getQualityPastSellIn($item);
                continue;
            }

            // All other items degrade in quality
            if (!in_array($item->name, GildedRose::increasesInQuality, true)) {
                // ItemName::CONJURED degrades in quality twice as fast compared to other items
                $degradeSpeed = $item->name == ItemName::CONJURED ? 2 : 1;

                $item->quality = max(GildedRose::minQuality, $item->quality - $degradeSpeed);
            }
        }
    }

    private function getQualityPastSellIn(Item $item): int
    {
        // ItemName::AGED_BRIE increases in quality the older it gets
        if ($item->name == ItemName::AGED_BRIE) {
            return min(GildedRose::maxQuality, $item->quality + 1);
        }

        // ItemName::BACKSTAGE_PASSES quality drops to 0 after the concert
        if ($item->name == ItemName::BACKSTAGE_PASSES) {
            return GildedRose::minQuality;
        }

        // ItemName::CONJURED degrades in quality twice as fast compared to other items
        if ($item->name == ItemName::CONJURED) {
            return max(GildedRose::minQuality, $item->quality - 4);
        }

        // All other items degrade in quality twice as fast
        return max(GildedRose::minQuality, $item->quality - 2);
    }

    private function getIncreasedQuality(Item $item): int
    {
        if ($item->name == ItemName::BACKSTAGE_PASSES) {
            // ItemName::BACKSTAGE_PASSES increases by 3 when there are 5 days or less
            if ($item->sellIn <= 5) {
                return min(GildedRose::maxQuality, $item->quality + 3);
            }

            // ItemName::BACKSTAGE_PASSES increases by 2 when there are 10 days or less
            if ($item->sellIn <= 10) {
                return min(GildedRose::maxQuality, $item->quality + 2);
            }
        }

        return min(GildedRose::maxQuality, $item->quality + 1);
    }
}

<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Entities;

use EugeneErg\Translate\ValueObjects\GroupId;
use EugeneErg\Translate\ValueObjects\TranslateId;

final readonly class Group
{
    public function __construct(
        public GroupId $id,
        public string $originalPattern,
        public string $pattern,
        public string $locale,
        public ?string $context = null,
    ) {
    }
}

//translate ( - собственно сам перевод, может переиспользоваться
//  id,
//  pattern - переведенный паттерн сообщения,
//  language - язык паттерна
//)
//group ( - группа, для набора связанных переводов
//  id,
//  pattern - паттерн выбора перевода (может зависеть от количества, селекта и т д),
//  context - контекст, использующийся для перевода
//)
//group_translates ( - наборы переводов, входящие в группу
//  group_id,
//  translate_id,
//  key - ключ паттерна группы, соответствующий переводу
//  ?source_id - с какого translate перевели
//)
//path ( - составные части файла
//  id,
//  ?parent_id,
//  ?group_id, - если есть, то нельзя иметь дочерние пути
//  value - если нет parent_id - название файла, если есть - ключ в этом файле/ массиве
//)
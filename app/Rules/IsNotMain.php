<?php

/**
 * IsBoolean.php
 * Copyright (c) 2019 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class IsBoolean
 */
class IsNotMain implements Rule
{
    public $model;
    public function __construct($model)
    {
        $this->model = $model;
    }
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return (string)trans('validation.main_categorie');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $result = $this->model::where($attribute, $value)->get()->first();

        if($result && $result->is_main){
            return false;
        }

        return true;
    }
}

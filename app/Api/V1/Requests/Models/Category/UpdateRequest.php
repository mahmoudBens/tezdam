<?php
/**
 * CategoryUpdateRequest.php
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

namespace FireflyIII\Api\V1\Requests\Models\Category;

use FireflyIII\Models\Category;
use FireflyIII\Rules\IsNotMain;
use FireflyIII\Support\Request\ChecksLogin;
use FireflyIII\Support\Request\ConvertsDataTypes;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateRequest
 *

 */
class UpdateRequest extends FormRequest
{
    use ConvertsDataTypes;
    use ChecksLogin;

    /**
     * Get all data from the request.
     *
     * @return array
     */
    public function getAll(): array
    {
        $fields = [
            'name'  => ['name', 'convertString'],
            'color'  => ['color', 'convertString'],
            'nature'  => ['nature', 'convertString'],
            'icon'  => ['icon', 'convertString'],
            'notes' => ['notes', 'stringWithNewlines'],
            'category'  => ['category', 'convertString'],
        ];

        return $this->getAllData($fields);
    }

    /**
     * The rules that the incoming request must be matched against.
     *
     * @return array
     */
    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route()->parameter('category');

        return [
            'name'      => ['between:1,100',new IsNotMain(Category::class),'uniqueObjectForUser:categories,name,'.$category->id],
            // 'color'     => ['sometimes','regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            // 'nature'    => 'sometimes|string',
            // 'icon'      => 'sometimes|string',
            // 'category'  =>['sometimes','nullable','exists:categories,name','not_in:'.$category->name],
        ];
    }
}

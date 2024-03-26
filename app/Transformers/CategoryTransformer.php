<?php
/**
 * CategoryTransformer.php
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

namespace FireflyIII\Transformers;

use FireflyIII\Models\Category;
use FireflyIII\Repositories\Category\CategoryRepositoryInterface;
use FireflyIII\Repositories\Category\OperationsRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CategoryTransformer
 */
class CategoryTransformer extends AbstractTransformer
{
    private OperationsRepositoryInterface $opsRepository;
    private CategoryRepositoryInterface   $repository;

    /**
     * CategoryTransformer constructor.
     *

     */
    public function __construct()
    {
        $this->opsRepository = app(OperationsRepositoryInterface::class);
        $this->repository    = app(CategoryRepositoryInterface::class);
        $this->user    = auth()->user();
    }

    /**
     * Convert category.
     *
     * @param Category $category
     *
     * @return array
     */
    public function transform(Category $category): array
    {
        $this->opsRepository->setUser($this->user);
        $this->repository->setUser($this->user);

        $spent  = [];
        $earned = [];
        $start  = $this->parameters->get('start');
        $end    = $this->parameters->get('end');
        if (null !== $start && null !== $end) {
            $earned = $this->beautify($this->opsRepository->sumIncome($start, $end, null, new Collection([$category])));
            $spent  = $this->beautify($this->opsRepository->sumExpenses($start, $end, null, new Collection([$category])));
        }
        $notes = $this->repository->getNoteText($category);
        $sub_categories = $category->sub_categories()->where('user_id', $this->user->id)->get()->all();
        
        return [
            'id'                => (int)$category->id,
            'created_at'        => $category->created_at->toAtomString(),
            'updated_at'        => $category->updated_at->toAtomString(),
            'name'              => $category->name,
            'color'             => $category->color,
            'nature'            => $category->nature,
            'icon'              => $category->icon,
            'is_main'           => $category->is_main,
            'parent_category'   => $category->parent_category?->name,
            'sub_category'      => array_map(function($item){
                                        return $this->sub_categorie_data($item);
                                    },$sub_categories),
            'notes'             => $notes,
            'spent'             => $spent,
            'earned'            => $earned,
            'links'             => [
                [
                    'rel' => 'self',
                    'uri' => '/categories/' . $category->id,
                ],
            ],
        ];
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function beautify(array $array): array
    {
        $return = [];
        foreach ($array as $data) {
            $data['sum'] = app('steam')->bcround($data['sum'], (int)$data['currency_decimal_places']);
            $return[]    = $data;
        }

        return $return;
    }

    private function sub_categorie_data(Category $sub):array
    {
        
        $spent  = [];
        $earned = [];
        $start  = $this->parameters->get('start');
        $end    = $this->parameters->get('end');
        if (null !== $start && null !== $end) {
            $earned = $this->beautify($this->opsRepository->sumIncome($start, $end, null, new Collection([$sub])));
            $spent  = $this->beautify($this->opsRepository->sumExpenses($start, $end, null, new Collection([$sub])));
        }
        $notes = $this->repository->getNoteText($sub);

        $sub_categories = $sub->sub_categories()->where('user_id', $this->user->id)->get()->all();

        return [
            'id'                => (string)$sub->id,
            'created_at'        => $sub->created_at->toAtomString(),
            'updated_at'        => $sub->updated_at->toAtomString(),
            'name'              => $sub->name,
            'color'             => $sub->color,
            'nature'            => $sub->nature,
            'icon'              => $sub->icon,
            'is_main'           => $sub->is_main,
            'sub_category'      => array_map(function($item){
                                    return $this->sub_categorie_data($item);
                                },$sub_categories),
            'notes'             => $notes,
            'spent'             => $spent,
            'earned'            => $earned,
            'links'             => [
                [
                    'rel' => 'self',
                    'uri' => '/categories/' . $sub->id,
                ],
            ],
        ];
    }
}

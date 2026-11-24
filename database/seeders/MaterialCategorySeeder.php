<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('material_categories')->insert([
            [
                'name' => 'Cement & Binder',
                'description' => 'Cement, lime, gypsum and other binding materials used for concrete, mortar, plastering and construction works.',
            ],
            [
                'name' => 'Sand & Aggregate',
                'description' => 'Fine and coarse aggregates including river sand, coarse sand, stone chips and gravel used in concrete and masonry works.',
            ],
            [
                'name' => 'Brick & Block',
                'description' => 'Clay bricks, concrete blocks, hollow blocks and AAC blocks used for walls, partitions and structural construction.',
            ],
            [
                'name' => 'Steel & Reinforcement',
                'description' => 'Reinforcement bars, steel rods, binding wire, structural steel and other metal materials used for structural strength.',
            ],
            [
                'name' => 'Stone & Concrete',
                'description' => 'Natural stones, ready-mix concrete, precast concrete products and other concrete-based construction materials.',
            ],
            [
                'name' => 'Roofing Materials',
                'description' => 'Roof sheets, roofing tiles, metal sheets, roof insulation and accessories used for roof construction and protection.',
            ],
            [
                'name' => 'Tiles & Flooring',
                'description' => 'Ceramic tiles, porcelain tiles, marble, granite, floor tiles and other materials used for floor and wall finishing.',
            ],
            [
                'name' => 'Plumbing Materials',
                'description' => 'PVC, CPVC, PPR and GI pipes, pipe fittings, valves and other materials used in water supply and drainage systems.',
            ],
            [
                'name' => 'Electrical Materials',
                'description' => 'Electrical cables, wires, switches, sockets, circuit breakers, conduits and accessories used in electrical installations.',
            ],
            [
                'name' => 'Paint & Coating',
                'description' => 'Interior and exterior paints, primers, varnishes, enamel, protective coatings and finishing materials.',
            ],
            [
                'name' => 'Wood & Timber',
                'description' => 'Timber, plywood, MDF boards, particle boards and other wood-based materials used for doors, furniture and construction.',
            ],
            [
                'name' => 'Glass & Aluminum',
                'description' => 'Glass panels, aluminum profiles, frames and accessories used for windows, partitions, facades and building interiors.',
            ],
            [
                'name' => 'Doors & Windows',
                'description' => 'Wooden, steel, aluminum and UPVC doors, windows, frames, shutters and related installation accessories.',
            ],
            [
                'name' => 'Waterproofing Materials',
                'description' => 'Waterproofing membranes, liquid coatings, bitumen products and protective materials used to prevent water leakage.',
            ],
            [
                'name' => 'Sanitary & Bathroom Fittings',
                'description' => 'Wash basins, commodes, shower sets, faucets, bathroom accessories and other sanitary installation products.',
            ],
            [
                'name' => 'Hardware & Fasteners',
                'description' => 'Nails, screws, bolts, nuts, hinges, locks, clamps and other hardware items used for fixing and joining construction components.',
            ],
            [
                'name' => 'Welding Materials',
                'description' => 'Welding electrodes, filler rods, welding wires and related consumables used for metal fabrication and joining works.',
            ],
            [
                'name' => 'Insulation Materials',
                'description' => 'Thermal and acoustic insulation materials including glass wool, rock wool, foam boards and insulation sheets.',
            ],
            [
                'name' => 'Ceiling & Partition Materials',
                'description' => 'Gypsum boards, false ceiling panels, metal channels, partition boards and framing materials used for interior construction.',
            ],
            [
                'name' => 'Safety Materials (PPE)',
                'description' => 'Personal protective equipment including safety helmets, gloves, safety shoes, goggles, masks and reflective vests.',
            ],
            [
                'name' => 'Construction Chemicals',
                'description' => 'Concrete admixtures, bonding agents, curing compounds, grouts and other specialty chemicals used in construction works.',
            ],
            [
                'name' => 'Formwork & Shuttering Materials',
                'description' => 'Shuttering plywood, formwork panels, timber supports, props and accessories used for concrete casting and shaping.',
            ],
            [
                'name' => 'Scaffolding Materials',
                'description' => 'Scaffolding pipes, couplers, base plates, frames, platforms and accessories used for temporary working structures.',
            ],
            [
                'name' => 'Road Construction Materials',
                'description' => 'Bitumen, asphalt, road base materials, paving blocks, road marking paint and other materials used in road construction.',
            ],
            [
                'name' => 'Landscaping Materials',
                'description' => 'Topsoil, grass, plants, decorative stones, garden paving materials and other supplies used for outdoor landscaping.',
            ],
            [
                'name' => 'HVAC Materials',
                'description' => 'Air ducts, duct insulation, copper pipes, refrigerant lines, ventilation fittings and accessories used in HVAC installations.',
            ],
            [
                'name' => 'Fire Safety Materials',
                'description' => 'Fire extinguishers, fire-rated doors, fire alarm cables, sprinkler pipes and other fire protection system materials.',
            ],
            [
                'name' => 'Adhesive & Sealant',
                'description' => 'Tile adhesives, silicone sealants, construction adhesives, epoxy compounds and other bonding and sealing products.',
            ],
            [
                'name' => 'Plumbing Accessories',
                'description' => 'Pipe clamps, connectors, unions, traps, hose connectors and other supporting accessories used in plumbing installations.',
            ],
            [
                'name' => 'Miscellaneous Materials',
                'description' => 'General construction supplies and utility items that do not belong to any specific material category.',
            ],
        ]);
    }
    }
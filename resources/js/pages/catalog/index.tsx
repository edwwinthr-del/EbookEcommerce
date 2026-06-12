import BookCard, { type CatalogBook } from '@/components/book-card';
import Pagination from '@/components/pagination';
import { Input } from '@/components/ui/input';
import StoreLayout from '@/layouts/store-layout';
import { type Paginated } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

interface CategoryOption {
    id: number;
    name: string;
    slug: string;
}

interface Filters {
    q: string;
    category: string;
    sort: string;
}

const selectClasses =
    'border-input flex h-9 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:ring-1 focus-visible:outline-none dark:bg-input/30';

export default function CatalogIndex({
    books,
    categories,
    filters,
}: {
    books: Paginated<CatalogBook>;
    categories: CategoryOption[];
    filters: Filters;
}) {
    const [query, setQuery] = useState(filters.q);
    const isFirstRender = useRef(true);

    const applyFilters = (next: Partial<Filters>) => {
        const params = { q: query, category: filters.category, sort: filters.sort, ...next };

        router.get(
            '/',
            Object.fromEntries(Object.entries(params).filter(([, value]) => value !== '')),
            { preserveState: true, replace: true },
        );
    };

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => applyFilters({ q: query }), 300);
        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [query]);

    return (
        <StoreLayout>
            <Head title="Catalog" />

            <div className="flex flex-col gap-6">
                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        type="search"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder='Try "cozy mystery" or "dragons but make it sad"...'
                        className="max-w-md"
                    />

                    <select
                        value={filters.category}
                        onChange={(e) => applyFilters({ category: e.target.value })}
                        className={selectClasses}
                        aria-label="Filter by category"
                    >
                        <option value="">All categories</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.slug}>
                                {category.name}
                            </option>
                        ))}
                    </select>

                    <select
                        value={filters.sort}
                        onChange={(e) => applyFilters({ sort: e.target.value })}
                        className={selectClasses}
                        aria-label="Sort"
                    >
                        <option value="">Newest first</option>
                        <option value="price_asc">Price: low to high</option>
                        <option value="price_desc">Price: high to low</option>
                    </select>
                </div>

                {books.data.length > 0 ? (
                    <div className="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
                        {books.data.map((book) => (
                            <BookCard key={book.id} book={book} />
                        ))}
                    </div>
                ) : (
                    <div className="py-16 text-center text-muted-foreground">
                        <p className="text-lg">No books found.</p>
                        <p className="text-sm">Try a different search — the dragons are hiding.</p>
                    </div>
                )}

                <Pagination links={books.links} />
            </div>
        </StoreLayout>
    );
}

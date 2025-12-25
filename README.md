# BelongsToMany Field for Laravel Nova

A custom BelongsToMany field for Laravel Nova that provides a multiselect interface with server-side search support, designed for handling large datasets efficiently.

## Features

- **Server-side search**: Search through thousands of records without loading them all
- **Debounced search**: 300ms debounce to prevent excessive API calls
- **Preserves selections**: Selected items remain visible while searching
- **Nova theme integration**: Uses Nova CSS variables for consistent styling
- **Dark mode support**: Fully compatible with Nova's dark mode
- **Dependent fields support**: Works with Nova's dependent fields feature

## Installation

1. Place the package in your `nova-components` directory
2. Register the package in your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./nova-components/Belongstomany"
        }
    ],
    "require": {
        "ostheneo/belongstomany": "*"
    }
}
```

3. Run composer update:

```bash
composer update
```

## Usage

### Basic Usage

```php
use Ostheneo\Belongstomany\Belongstomany;

public function fields(NovaRequest $request): array
{
    return [
        Belongstomany::make('Tags', 'tags', Tag::class)
            ->optionsLabel('name'),
    ];
}
```

### With Server-Side Search (Recommended for large datasets)

```php
Belongstomany::make('Tags', 'tags', Tag::class)
    ->optionsLabel('name')
    ->searchable()
    ->optionsLimit(50),
```

### Full Example

```php
Belongstomany::make('Tags', 'tags', Tag::class)
    ->optionsLabel('name')
    ->searchable()
    ->optionsLimit(50)
    ->viewable()
    ->rules('required'),
```

## Available Methods

### `optionsLabel(string $field)`

Specifies which field to display as the label in the multiselect dropdown.

```php
->optionsLabel('name')  // Will display the 'name' field of related models
```

### `searchable(bool $searchable = true)`

Enables server-side search. When enabled, the field will fetch options from the server based on user input instead of loading all options upfront.

```php
->searchable()  // Enable server-side search
```

### `optionsLimit(int $limit)`

Sets the maximum number of options to load. Works both with and without `searchable()`:
- **With `searchable()`**: Limits results returned per search query
- **Without `searchable()`**: Limits initial options loaded (uses local filtering)

```php
->optionsLimit(50)  // Load maximum 50 options
```

### `viewable(bool $viewable = true)`

When enabled, selected items will be clickable links to their detail pages.

```php
->viewable()  // Make selected items clickable
```

### `rules($rules)`

Apply validation rules to the field.

```php
->rules('required')
->rules(['required', 'min:1'])
```

### `setPivot(array $attributes)`

Set additional pivot table attributes when syncing relationships.

```php
->setPivot(['added_by' => auth()->id()])
```

### `setMultiselectProps(array $props)`

Customize vue-multiselect component labels.

```php
->setMultiselectProps([
    'selectLabel' => 'Press enter to select',
    'deselectLabel' => 'Press enter to remove',
    'selectedLabel' => 'Selected',
])
```

### `setMultiselectSlots(array $slots)`

Customize empty state messages.

```php
->setMultiselectSlots([
    'noOptions' => 'No items available',
    'noResult' => 'No matching items found',
])
```

### `showAsListInDetail(bool $show = true)`

Display selected items as a list on the detail page.

```php
->showAsListInDetail()
```

## Model Requirements

Your related model must have the standard BelongsToMany relationship defined:

```php
// App\Models\Post.php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

And the inverse relationship (optional but recommended):

```php
// App\Models\Tag.php
public function posts()
{
    return $this->belongsToMany(Post::class);
}
```

## Server-Side Search

When `searchable()` is enabled, the field performs server-side searches with the following behavior:

1. **Initial load**: Fetches up to `optionsLimit` records
2. **On search**: Sends search query to server with 300ms debounce
3. **Preserves selections**: Currently selected items remain visible even if not in search results

The search is performed on the field specified in `optionsLabel()`.

## Styling

The field uses Nova's CSS variables for consistent theming:

- `--colors-primary-500` / `--colors-primary-600` for selected tags
- `--colors-gray-*` for borders and backgrounds
- Automatic dark mode support

## License

MIT License

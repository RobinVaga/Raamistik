<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class MovieController extends Controller
{
    private string $apiKey;
    private string $apiToken;
    private string $baseUrl = 'https://api.themoviedb.org/3';
    private string $imageBaseUrl = 'https://image.tmdb.org/t/p';

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
        $this->apiToken = config('services.tmdb.api_token');
    }

    /**
     * Display a listing of movies with filters and search
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|string|in:popularity.desc,popularity.asc,release_date.desc,release_date.asc,vote_average.desc,vote_average.asc',
            'genre' => 'nullable|integer',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 2),
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $search = $validated['search'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'popularity.desc';
        $genre = $validated['genre'] ?? null;
        $year = $validated['year'] ?? null;
        $limit = $validated['limit'] ?? 20;
        $page = $validated['page'] ?? 1;

        // Create cache key based on filters
        $cacheKey = "movies_{$search}_{$sortBy}_{$genre}_{$year}_{$limit}_{$page}";

        // Cache for 1 hour
        $movies = Cache::remember($cacheKey, 3600, function () use ($search, $sortBy, $genre, $year, $page) {
            if ($search) {
                return $this->searchMovies($search, $page);
            }
            return $this->discoverMovies($sortBy, $genre, $year, $page);
        });

        // Get genres for filter dropdown
        $genres = Cache::remember('movie_genres', 86400, function () {
            return $this->getGenres();
        });

        // Limit results if specified
        if (isset($movies['results']) && $limit < 20) {
            $movies['results'] = array_slice($movies['results'], 0, $limit);
        }

        return Inertia::render('movies/Index', [
            'movies' => $movies,
            'genres' => $genres,
            'filters' => [
                'search' => $search,
                'sort_by' => $sortBy,
                'genre' => $genre,
                'year' => $year,
                'limit' => $limit,
                'page' => $page,
            ],
        ]);
    }

    /**
     * Display the specified movie
     */
    public function show(int $id): Response
    {
        $cacheKey = "movie_{$id}";

        $movie = Cache::remember($cacheKey, 3600, function () use ($id) {
            return $this->getMovieDetails($id);
        });

        if (!$movie) {
            abort(404, 'Movie not found');
        }

        return Inertia::render('movies/Show', [
            'movie' => $movie,
        ]);
    }

    /**
     * Search movies by query
     */
    private function searchMovies(string $query, int $page = 1): array
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->get("{$this->baseUrl}/search/movie", [
                    'query' => $query,
                    'page' => $page,
                    'language' => 'en-US',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatMoviesResponse($data);
            }

            Log::error('TMDB API search error', ['status' => $response->status()]);
            return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => 1];
        } catch (\Exception $e) {
            Log::error('TMDB API search exception', ['message' => $e->getMessage()]);
            return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => 1];
        }
    }

    /**
     * Discover movies with filters
     */
    private function discoverMovies(string $sortBy, ?int $genre, ?int $year, int $page = 1): array
    {
        try {
            $params = [
                'sort_by' => $sortBy,
                'page' => $page,
                'language' => 'en-US',
            ];

            if ($genre) {
                $params['with_genres'] = $genre;
            }

            if ($year) {
                $params['primary_release_year'] = $year;
            }

            $response = Http::withToken($this->apiToken)
                ->get("{$this->baseUrl}/discover/movie", $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatMoviesResponse($data);
            }

            Log::error('TMDB API discover error', ['status' => $response->status()]);
            return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => 1];
        } catch (\Exception $e) {
            Log::error('TMDB API discover exception', ['message' => $e->getMessage()]);
            return ['results' => [], 'total_results' => 0, 'total_pages' => 0, 'page' => 1];
        }
    }

    /**
     * Get movie details by ID
     */
    public function getMovieDetails(int $id): ?array
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->get("{$this->baseUrl}/movie/{$id}", [
                    'language' => 'en-US',
                    'append_to_response' => 'credits,videos,similar',
                ]);

            if ($response->successful()) {
                $movie = $response->json();
                return $this->formatMovieDetails($movie);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('TMDB API movie details exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get list of genres
     */
    private function getGenres(): array
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->get("{$this->baseUrl}/genre/movie/list", [
                    'language' => 'en-US',
                ]);

            if ($response->successful()) {
                return $response->json()['genres'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('TMDB API genres exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Format movies list response
     */
    private function formatMoviesResponse(array $data): array
    {
        return [
            'results' => array_map(function ($movie) {
                return [
                    'id' => $movie['id'],
                    'title' => $movie['title'],
                    'overview' => $movie['overview'] ?? '',
                    'poster_path' => $movie['poster_path'] ? "{$this->imageBaseUrl}/w500{$movie['poster_path']}" : null,
                    'backdrop_path' => $movie['backdrop_path'] ? "{$this->imageBaseUrl}/original{$movie['backdrop_path']}" : null,
                    'release_date' => $movie['release_date'] ?? null,
                    'vote_average' => $movie['vote_average'] ?? 0,
                    'vote_count' => $movie['vote_count'] ?? 0,
                    'popularity' => $movie['popularity'] ?? 0,
                    'genre_ids' => $movie['genre_ids'] ?? [],
                ];
            }, $data['results'] ?? []),
            'total_results' => $data['total_results'] ?? 0,
            'total_pages' => $data['total_pages'] ?? 0,
            'page' => $data['page'] ?? 1,
        ];
    }

    /**
     * Format movie details response
     */
private function formatMovieDetails(array $movie): array
{
    // Ensure videos is always an array
    $videos = [];
    if (isset($movie['videos']['results']) && is_array($movie['videos']['results'])) {
        $videos = array_values(array_filter($movie['videos']['results'], function ($video) {
            return isset($video['site']) && $video['site'] === 'YouTube' 
                && isset($video['type']) && $video['type'] === 'Trailer';
        }));
    }

    // Process director with profile path
    $director = collect($movie['credits']['crew'] ?? [])
        ->firstWhere('job', 'Director');
    
    if ($director && isset($director['profile_path']) && $director['profile_path']) {
        $director['profile_path'] = "{$this->imageBaseUrl}/w185{$director['profile_path']}";
    }

    return [
        'id' => $movie['id'],
        'title' => $movie['title'],
        'tagline' => $movie['tagline'] ?? '',
        'overview' => $movie['overview'] ?? '',
        'poster_path' => $movie['poster_path'] ? "{$this->imageBaseUrl}/w500{$movie['poster_path']}" : null,
        'backdrop_path' => $movie['backdrop_path'] ? "{$this->imageBaseUrl}/original{$movie['backdrop_path']}" : null,
        'release_date' => $movie['release_date'] ?? null,
        'runtime' => $movie['runtime'] ?? 0,
        'vote_average' => $movie['vote_average'] ?? 0,
        'vote_count' => $movie['vote_count'] ?? 0,
        'popularity' => $movie['popularity'] ?? 0,
        'budget' => $movie['budget'] ?? 0,
        'revenue' => $movie['revenue'] ?? 0,
        'genres' => $movie['genres'] ?? [],
        'production_companies' => array_map(function ($company) {
            return [
                'id' => $company['id'],
                'name' => $company['name'],
                'logo_path' => isset($company['logo_path']) && $company['logo_path'] 
                    ? "{$this->imageBaseUrl}/w185{$company['logo_path']}" 
                    : null,
                'origin_country' => $company['origin_country'] ?? '',
            ];
        }, $movie['production_companies'] ?? []),
        'cast' => array_map(function ($member) {
            return [
                'id' => $member['id'],
                'name' => $member['name'],
                'character' => $member['character'] ?? '',
                'profile_path' => isset($member['profile_path']) && $member['profile_path']
                    ? "{$this->imageBaseUrl}/w185{$member['profile_path']}"
                    : null,
                'order' => $member['order'] ?? 999,
            ];
        }, array_slice($movie['credits']['cast'] ?? [], 0, 10)),
        'director' => $director,
        'videos' => $videos,
        'similar' => array_slice(
            array_map(function ($similar) {
                return [
                    'id' => $similar['id'],
                    'title' => $similar['title'],
                    'poster_path' => $similar['poster_path'] ? "{$this->imageBaseUrl}/w500{$similar['poster_path']}" : null,
                    'vote_average' => $similar['vote_average'] ?? 0,
                ];
            }, $movie['similar']['results'] ?? []),
            0,
            12
        ),
    ];
}
}
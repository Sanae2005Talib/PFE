import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/association.dart';
import '../models/region.dart';
import 'association_detail.dart';

/// Liste des associations avec filtres
/// Affiche toutes les associations validées
class AssociationsListScreen extends StatefulWidget {
  const AssociationsListScreen({super.key});

  @override
  State<AssociationsListScreen> createState() => _AssociationsListScreenState();
}

class _AssociationsListScreenState extends State<AssociationsListScreen> {
  final ApiService _apiService = ApiService();
  
  // Données
  List<Association> _associations = [];
  List<Region> _regions = [];
  
  // État
  bool _loading = true;
  
  // Filtres
  int? _selectedRegionId;
  String _searchQuery = '';
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  /// Charger les données depuis l'API
  Future<void> _loadData() async {
    setState(() => _loading = true);
    
    // Charger régions et associations en parallèle
    final regions = await _apiService.getRegions();
    final associations = await _apiService.getAssociations(
      regionId: _selectedRegionId,
      search: _searchQuery.isEmpty ? null : _searchQuery,
    );
    
    setState(() {
      _regions = regions;
      _associations = associations;
      _loading = false;
    });
  }

  /// Effacer les filtres
  void _clearFilters() {
    setState(() {
      _selectedRegionId = null;
      _searchQuery = '';
      _searchController.clear();
    });
    _loadData();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Associations'),
        actions: [
          // Bouton refresh
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
          ),
        ],
      ),
      
      body: Column(
        children: [
          // Section Filtres
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.grey[100],
            child: Column(
              children: [
                // Barre de recherche
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Rechercher une association...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: _searchQuery.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _searchController.clear();
                              setState(() => _searchQuery = '');
                              _loadData();
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  onChanged: (value) {
                    setState(() => _searchQuery = value);
                  },
                  onSubmitted: (_) => _loadData(),
                ),
                
                const SizedBox(height: 12),
                
                // Filtre par région
                DropdownButtonFormField<int>(
                  initialValue: _selectedRegionId,
                  decoration: InputDecoration(
                    labelText: 'Filtrer par région',
                    prefixIcon: const Icon(Icons.location_on),
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  items: [
                    const DropdownMenuItem(
                      value: null,
                      child: Text('Toutes les régions'),
                    ),
                    ..._regions.map((region) => DropdownMenuItem(
                          value: region.id,
                          child: Text(region.name),
                        )),
                  ],
                  onChanged: (value) {
                    setState(() => _selectedRegionId = value);
                    _loadData();
                  },
                ),
                
                // Bouton effacer filtres
                if (_selectedRegionId != null || _searchQuery.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: TextButton.icon(
                      onPressed: _clearFilters,
                      icon: const Icon(Icons.clear_all),
                      label: const Text('Effacer les filtres'),
                    ),
                  ),
              ],
            ),
          ),
          
          // Liste des associations
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _associations.isEmpty
                    ? _buildEmptyState()
                    : RefreshIndicator(
                        onRefresh: _loadData,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _associations.length,
                          itemBuilder: (context, index) {
                            return _buildAssociationCard(_associations[index]);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  /// Widget pour état vide
  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.business_outlined,
            size: 80,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            'Aucune association trouvée',
            style: TextStyle(
              fontSize: 18,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  /// Card pour une association
  Widget _buildAssociationCard(Association association) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: InkWell(
        onTap: () {
          // Navigation vers détails
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => AssociationDetailScreen(
                association: association,
              ),
            ),
          );
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Icône association
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Theme.of(context).primaryColor,
                      Theme.of(context).colorScheme.secondary,
                    ],
                  ),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.business,
                  color: Colors.white,
                  size: 30,
                ),
              ),
              
              const SizedBox(width: 16),
              
              // Infos association
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Nom
                    Text(
                      association.associationName,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    
                    const SizedBox(height: 4),
                    
                    // Région
                    Row(
                      children: [
                        Icon(
                          Icons.location_on,
                          size: 16,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            association.regionName ?? 'Non spécifié',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[600],
                            ),
                          ),
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: 4),
                    
                    // Nombre de besoins actifs
                    Row(
                      children: [
                        Icon(
                          Icons.volunteer_activism,
                          size: 16,
                          color: Theme.of(context).primaryColor,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '${association.activeNeedsCount} besoin(s) actif(s)',
                          style: TextStyle(
                            fontSize: 14,
                            color: Theme.of(context).primaryColor,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              
              // Icône flèche
              Icon(
                Icons.arrow_forward_ios,
                size: 16,
                color: Colors.grey[400],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'associations_list.dart';
import 'home_screen.dart';

/// Dashboard Admin
/// Cards avec statistiques + Drawer pour navigation
class DashboardScreen extends StatefulWidget {
  final String userName;
  final String userRole;

  const DashboardScreen({
    super.key,
    required this.userName,
    required this.userRole,
  });

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiService _apiService = ApiService();
  
  // Statistiques
  int _totalAssociations = 0;
  int _totalBesoins = 0;
  int _urgentBesoins = 0;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  /// Charger les statistiques
  Future<void> _loadStats() async {
    setState(() => _loading = true);
    
    try {
      // Récupérer toutes les associations
      final associations = await _apiService.getAssociations();
      
      // Compter total besoins et urgents
      int totalBesoins = 0;
      int urgentBesoins = 0;
      
      for (var association in associations) {
        final details = await _apiService.getAssociationDetails(association.id);
        if (details != null && details['stats'] != null) {
          totalBesoins += (details['stats']['total_needs'] ?? 0) as int;
          urgentBesoins += (details['stats']['urgent_needs'] ?? 0) as int;
        }
      }
      
      setState(() {
        _totalAssociations = associations.length;
        _totalBesoins = totalBesoins;
        _urgentBesoins = urgentBesoins;
        _loading = false;
      });
    } catch (e) {
      setState(() => _loading = false);
      print('Erreur chargement stats: $e');
    }
  }

  /// Logout
  void _logout() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Déconnexion'),
        content: const Text('Voulez-vous vraiment vous déconnecter ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              Navigator.pushReplacement(
                context,
                MaterialPageRoute(builder: (_) => const HomeScreen()),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: const Text('Déconnexion'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard Admin'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadStats,
            tooltip: 'Actualiser',
          ),
        ],
      ),
      
      // Drawer Menu
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            // Header
            DrawerHeader(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Theme.of(context).primaryColor,
                    Theme.of(context).colorScheme.secondary,
                  ],
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  const CircleAvatar(
                    radius: 30,
                    backgroundColor: Colors.white,
                    child: Icon(
                      Icons.admin_panel_settings,
                      size: 35,
                      color: Color(0xFF10B981),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    widget.userName,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Text(
                    widget.userRole.toUpperCase(),
                    style: const TextStyle(
                      color: Colors.white70,
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            ),
            
            // Menu Dashboard
            ListTile(
              leading: const Icon(Icons.dashboard),
              title: const Text('Dashboard'),
              selected: true,
              onTap: () => Navigator.pop(context),
            ),
            
            const Divider(),
            
            // Menu Associations
            ListTile(
              leading: const Icon(Icons.business),
              title: const Text('Associations'),
              subtitle: Text('$_totalAssociations association(s)'),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const AssociationsListScreen(),
                  ),
                );
              },
            ),
            
            // Menu Besoins
            ListTile(
              leading: const Icon(Icons.volunteer_activism),
              title: const Text('Tous les besoins'),
              subtitle: Text('$_totalBesoins besoin(s)'),
              onTap: () {
                Navigator.pop(context);
                // Navigation vers liste globale des besoins
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Consultez les besoins via les associations'),
                  ),
                );
              },
            ),
            
            const Divider(),
            
            // Menu Consultation publique
            ListTile(
              leading: const Icon(Icons.public),
              title: const Text('Vue publique'),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const HomeScreen(),
                  ),
                );
              },
            ),
            
            const Divider(),
            
            // Logout
            ListTile(
              leading: const Icon(Icons.logout, color: Colors.red),
              title: const Text(
                'Déconnexion',
                style: TextStyle(color: Colors.red),
              ),
              onTap: _logout,
            ),
          ],
        ),
      ),
      
      // Body - Cards avec statistiques
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadStats,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Titre de bienvenue
                    Text(
                      'Bienvenue, ${widget.userName}!',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    
                    const SizedBox(height: 8),
                    
                    Text(
                      'Voici un aperçu de votre plateforme',
                      style: TextStyle(
                        fontSize: 16,
                        color: Colors.grey[600],
                      ),
                    ),
                    
                    const SizedBox(height: 24),
                    
                    // Grid de Cards
                    GridView.count(
                      crossAxisCount: 2,
                      crossAxisSpacing: 16,
                      mainAxisSpacing: 16,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      children: [
                        // Card Associations
                        _buildStatCard(
                          title: 'Associations',
                          value: _totalAssociations.toString(),
                          icon: Icons.business,
                          color: Colors.blue,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const AssociationsListScreen(),
                              ),
                            );
                          },
                        ),
                        
                        // Card Besoins
                        _buildStatCard(
                          title: 'Besoins',
                          value: _totalBesoins.toString(),
                          icon: Icons.volunteer_activism,
                          color: Colors.green,
                          onTap: () {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Consultez les besoins via les associations'),
                              ),
                            );
                          },
                        ),
                        
                        // Card Besoins Urgents
                        _buildStatCard(
                          title: 'Urgents',
                          value: _urgentBesoins.toString(),
                          icon: Icons.warning,
                          color: Colors.orange,
                          onTap: () {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('$_urgentBesoins besoin(s) urgent(s)'),
                                backgroundColor: Colors.orange,
                              ),
                            );
                          },
                        ),
                        
                        // Card Satisfaits
                        _buildStatCard(
                          title: 'Satisfaits',
                          value: (_totalBesoins - _urgentBesoins).toString(),
                          icon: Icons.check_circle,
                          color: Colors.teal,
                          onTap: () {},
                        ),
                      ],
                    ),
                    
                    const SizedBox(height: 24),
                    
                    // Actions rapides
                    const Text(
                      'Actions rapides',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    
                    const SizedBox(height: 16),
                    
                    // Boutons d'actions
                    _buildActionButton(
                      icon: Icons.business,
                      label: 'Voir toutes les associations',
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const AssociationsListScreen(),
                          ),
                        );
                      },
                    ),
                    
                    const SizedBox(height: 12),
                    
                    _buildActionButton(
                      icon: Icons.public,
                      label: 'Vue publique de l\'application',
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const HomeScreen(),
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  /// Widget Card de statistique
  Widget _buildStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  size: 40,
                  color: color,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                value,
                style: TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                title,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[700],
                  fontWeight: FontWeight.w600,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// Widget Bouton d'action
  Widget _buildActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return Card(
      child: ListTile(
        leading: Icon(
          icon,
          color: Theme.of(context).primaryColor,
        ),
        title: Text(label),
        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
        onTap: onTap,
      ),
    );
  }
}

<?php

namespace App\Services;

use App\Interfaces\FormateurInterface;
use App\Models\Customer;
use App\Traits\HasFormateur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FormateurService implements FormateurInterface
{
    use HasFormateur;

    public function index($idCustomer): mixed
    {
        $typeCustomer = Customer::typeCustomer();

        switch ($typeCustomer) {
            // CFP
            case 1:
                $forms = DB::table('v_cfp_formateurs')
                    ->select('idFormateur', 'idCfp', 'isActiveFormateur AS form_is_active', 'isActiveCfp AS cfp_is_active', 'form_initial_name', 'form_photo', 'form_name', 'form_firstname', 'form_email', 'form_phone')
                    ->where('idCfp', $idCustomer)
                    ->where('isActiveCfp', 1)
                    ->groupBy('idFormateur', 'idCfp', 'isActiveFormateur', 'isActiveCfp', 'form_initial_name', 'form_photo', 'form_name', 'form_firstname', 'form_email', 'form_phone')
                    ->orderBy('form_name', 'asc');

                return $forms;

                break;
            // Entreprise
            case 2:
                $forms = DB::table('v_formateur_internes')
                    ->select('idFormateur', 'idEntreprise', 'form_initial_name', 'form_photo', 'form_name', 'form_firstname', 'form_email', 'form_phone')
                    ->where('idEntreprise', $idCustomer)
                    ->orderBy('form_name', 'asc');

                return $forms;

                break;
            default:
                return null;
                break;
        }
    }

    public function edit($idCustomer, $idFormateur): mixed
    {
        $formateur = $this->index($idCustomer)->where('idFormateur', $idFormateur);

        return $formateur;
    }

    public function storeFormateur($idCustomer, $idFormateur, $idTypeFormateur): void
    {
        DB::transaction(function () use ($idCustomer, $idFormateur, $idTypeFormateur) {
            DB::table('forms')->insert([
                'idFormateur' => $idFormateur,
                'idTypeFormateur' => $idTypeFormateur,
                'idSexe' => 1
            ]);

            switch ($idTypeFormateur) {
                case 1:
                    DB::table('formateurs')->insert([
                        'idFormateur' => $idFormateur,
                        'idSp' => 1
                    ]);
                    break;
                case 2:
                    DB::table('formateur_internes')->insert([
                        'idFormateur'    => $idFormateur,
                        'idEmploye'      => $idFormateur,
                        'idEntreprise'   => $idCustomer
                    ]);
                    break;
                default:
                    return null;
                    break;
            }
        });
    }

    public function storeCfpFormateur($idCustomer, $idFormateur, $isActiveFormateur, $isActiveCfp): void
    {
        DB::table('cfp_formateurs')->insert([
            'idCfp' => $idCustomer,
            'idFormateur' => $idFormateur,
            'dateCollaboration' => Carbon::now(),
            'isActiveFormateur' => $isActiveFormateur,
            'isActiveCfp' => $isActiveCfp
        ]);
    }

    public function update($idCustomer, $idFormateur, $name, $firstname, $email, $phone = null): void
    {
        DB::table('users')->where('id', $idFormateur)->update([
            'name' => $name,
            'firstName' => $firstname,
            'email' => $email,
            'phone' => $phone
        ]);
    }

    public function updatePhoto($idCustomer, $idFormateur, $query, $imageFile): void
    {
        $form = $query->first();

        $manager = new ImageManager(new Driver());

        $image_parts = explode(";base64,", $imageFile);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $image = $manager->read($image_base64)->toWebp(25);
        $imageName = uniqid() . '.webp';
        $filePath = 'img/formateurs/' . $imageName;

        DB::transaction(function () use ($idFormateur, $form, $filePath, $image, $imageName) {
            if (!empty($form->form_photo)) {
                Storage::disk('do')->delete('img/formateurs/' . $form->form_photo);
            }

            // Upload the image to DigitalOcean Space
            Storage::disk('do')->put($filePath, $image, 'public');

            // Update the database with the new image name
            DB::table('users')->where('id', $idFormateur)->update([
                'photo' => $imageName,
            ]);
        });
    }

    public function destroy($idCustomer, $idFormateur): void
    {
        DB::transaction(function () use ($idCustomer, $idFormateur) {
            $typeCustomer = Customer::typeCustomer();

            if ($this->isFormatorHasExperience($idFormateur)) {
                DB::table('experiences')->where('id', $idFormateur)->delete();
            }

            if ($this->isFormatorHasDegree($idFormateur)) {
                DB::table('diplomes')->where('id', $idFormateur)->delete();
            }

            if ($this->isFormatorHasSkill($idFormateur)) {
                DB::table('competences')->where('id', $idFormateur)->delete();
            }

            if ($this->isFormatorHasLanguage($idFormateur)) {
                DB::table('langues')->where('id', $idFormateur)->delete();
            }

            if ($typeCustomer == 2) {
                DB::table('formateur_internes')->where('idEntreprise', $idCustomer)->where('idFormateur', $idFormateur)->delete();
            }

            if ($typeCustomer == 1) {
                DB::table('cfp_formateurs')->where('idCfp', $idCustomer)->where('idFormateur', $idFormateur)->delete();
                DB::table('formateurs')->where('idFormateur', $idFormateur)->delete();
            }

            DB::table('role_users')->where('user_id', $idFormateur)->delete();
            DB::table('forms')->where('idFormateur', $idFormateur)->delete();

            if ($typeCustomer == 2) {
                DB::table('employes')->where('idCustomer', $idCustomer)->where('idEmploye', $idFormateur)->delete();
            }

            DB::table('users')->where('id', $idFormateur)->update([
                'user_is_deleted' => 1
            ]);
        });
    }
}

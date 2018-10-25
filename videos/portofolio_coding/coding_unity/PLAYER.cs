using System.Collections;

using UnityEngine;
using UnityEngine.UI;

public class PLAYER : MonoBehaviour {
	private AudioSource mulaisound;
	public AudioClip soundmulai;
	public GUISkin skin;
	public GameObject player1;
	public GameObject computer;
	public GameObject Daduu;
	public GameObject [] plat;

	public int redplayergoto, bluplayergoto;

	public int Redplayerpos, blueplayerpos, Counterpos, targetpos;
	public GameObject kartuantartika;
	public bool redplayermove, blueplayermove, redplayermovespecial, blueplayermovespecial;
	public GameObject sukutoraja;
	public float speed;
	public GameObject objek;
	GameObject objekclone;
	public GameObject btnn;
	 


	public int[] coba =new int[32];

	// Use this for initialization
	void Start () {
		StartCoroutine (waktu_mulai ());
		Redplayerpos = 0;
		blueplayerpos = 0;
		redplayermove = false;
		blueplayermove = false;
		redplayermovespecial = false;
		blueplayermovespecial = false;
	
	}
	void Awake () {

		mulaisound = GetComponent<AudioSource>();

	}
	IEnumerator waktu_mulai(){

		yield return new WaitForSeconds (2);
		mulaisound.PlayOneShot (soundmulai);

	}

	// Update is called once per frame
	void Update () {
		if (redplayermove) {
			player1.transform.position = Vector3.MoveTowards (player1.transform.position, plat [Redplayerpos].transform.position, 0.5f);
			if (Vector3.Distance (player1.transform.position, plat [Redplayerpos].transform.position) < 0.3f) {
				if (Counterpos < targetpos) {
					
					Counterpos++;
					Redplayerpos++;
				
				}




				else {
					if (Redplayerpos == plat.Length - 1) {
						Debug.Log ("okeeee");


							
					}

				}
			}

	

		}else
			if (blueplayermove) {
				computer.transform.position = Vector3.MoveTowards (computer.transform.position, plat [blueplayerpos].transform.position, 0.5f);
				if (Vector3.Distance (computer.transform.position, plat [blueplayerpos].transform.position) < 0.1f) {
					if (Counterpos < targetpos) {

						Counterpos++;
						blueplayerpos++;
						if (blueplayerpos >= 31) {
							blueplayerpos -= 31;
							Counterpos -= 31;
							targetpos -= 31;
						}

					} else {
						if (blueplayerpos == plat.Length - 1) {
							blueplayermove = true;

						}

					}
				}



			}		


	}

	public void Execredplayermove(){

			redplayermove = true;

			targetpos = Daduu.GetComponent<dadut> ().dadunumber;
			if ((targetpos + Redplayerpos) < plat.Length) {
				Redplayerpos++;
				Counterpos = 1;
			}

	}

	public void btn(){
		Execredplayermove ();
		objekclone = Instantiate (objek, transform.position, Quaternion.identity) as GameObject;
	}

	public void Execblueplayermove(){
		blueplayermove = true;

		targetpos = Daduu.GetComponent<dadut> ().dadunumber;
		if ((targetpos + blueplayerpos) < plat.Length) {
			blueplayerpos++;
			Counterpos = 1;
		}
	}

	void OnGUI(){
		
			if (this.skin != null) {
				GUI.skin = this.skin;
			}
			GUILayout.Space (40);
			GUILayout.BeginHorizontal ();
			
			GUILayout.EndHorizontal ();
			GUILayout.BeginHorizontal ();
			
			GUILayout.EndHorizontal ();


	}
}
